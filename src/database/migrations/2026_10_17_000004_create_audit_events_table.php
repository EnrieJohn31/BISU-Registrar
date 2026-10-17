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
        // Append-only: the application never updates or deletes rows. In production,
        // the application's database user should have no UPDATE/DELETE on this table.
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('occurred_at', 6);
            $table->uuid('correlation_id');
            $table->string('actor_type', 20);
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('office_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->string('outcome', 20);
            $table->text('reason')->nullable();
            $table->json('changes')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->index('occurred_at');
            $table->index('correlation_id');
            $table->index(['actor_user_id', 'occurred_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['action', 'occurred_at']);
            $table->index(['office_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
