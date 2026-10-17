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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('remember_token');
            $table->foreignId('deactivated_by_id')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
            $table->string('deactivation_reason', 500)->nullable()->after('deactivated_by_id');
            $table->boolean('must_change_password')->default(false)->after('deactivation_reason');
            $table->timestamp('temporary_password_expires_at')->nullable()->after('must_change_password');
            $table->timestamp('last_login_at')->nullable()->after('temporary_password_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deactivated_by_id');
            $table->dropColumn([
                'deactivated_at',
                'deactivation_reason',
                'must_change_password',
                'temporary_password_expires_at',
                'last_login_at',
            ]);
        });
    }
};
