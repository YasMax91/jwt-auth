<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'refresh_token')) {
                $table->string('refresh_token')->nullable()->after('remember_token');
                $table->index('refresh_token');
            }

            // Add email index if it doesn't exist
            if (!Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'refresh_token')) {
                $table->dropIndex(['refresh_token']);
                $table->dropColumn('refresh_token');
            }
        });
    }
};
