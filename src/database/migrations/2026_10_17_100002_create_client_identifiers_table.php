<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->string('display_value', 64);

            // Compared exactly: a binary collation stops MySQL's default
            // case/accent-insensitive collation from treating different IDs as equal.
            $canonical = $table->string('canonical_value', 64);
            if (DB::getDriverName() === 'mysql') {
                $canonical->collation('utf8mb4_bin');
            }

            $table->unsignedSmallInteger('normalization_version');
            $table->foreignId('added_by_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('added_at');
            $table->timestamp('retired_at')->nullable();
            $table->foreignId('retired_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('retire_reason', 500)->nullable();

            // Historical identifiers stay in the key, so a retired ID is never reused
            // by another person (System_Plan Part 04).
            $table->unique(['type', 'canonical_value'], 'client_identifiers_type_value_unique');
            $table->index(['client_profile_id', 'retired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_identifiers');
    }
};
