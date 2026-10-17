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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('role', 50);
            $table->foreignId('office_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('assigned_at');
            $table->string('assignment_reason', 500);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('revoke_reason', 500)->nullable();

            // MySQL has no partial indexes: these keys make "one active assignment
            // per user, role and office" enforceable by a plain unique index.
            $table->unsignedBigInteger('office_key')->storedAs('COALESCE(office_id, 0)');
            $table->unsignedTinyInteger('active_key')->nullable()->storedAs('CASE WHEN revoked_at IS NULL THEN 1 ELSE NULL END');

            $table->unique(['user_id', 'role', 'office_key', 'active_key'], 'user_roles_one_active_unique');
            $table->index(['role', 'revoked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
