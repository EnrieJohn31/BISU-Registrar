<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Append-only history of every change to a profile or its identifiers. It holds
        // the old and new values, so it is shown only to staff who may view clients;
        // the general audit trail records no client names or full identifiers.
        Schema::create('client_profile_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('action', 30);
            $table->json('changes')->nullable();
            $table->string('reason', 500);
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('occurred_at', 6);

            // Named explicitly: the generated name exceeds MySQL's 64-character limit.
            $table->unique(['client_profile_id', 'revision_number'], 'client_revisions_profile_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profile_revisions');
    }
};
