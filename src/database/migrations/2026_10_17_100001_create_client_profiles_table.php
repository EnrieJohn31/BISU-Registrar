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
        // A client profile identifies a requester. It is not an academic record and
        // never grants login access (System_Plan Part 04). Profiles are never deleted
        // once created: later requests will reference them.
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->restrictOnDelete();
            $table->string('display_name');
            $table->string('category', 20)->default('unknown');
            $table->string('verification_state', 20)->default('unverified');
            $table->string('source_reference');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            // Optimistic lock: every change increments it and records a revision with
            // the same number, so a stale screen cannot overwrite a newer edit.
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->index(['office_id', 'verification_state']);
            $table->index(['office_id', 'display_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
