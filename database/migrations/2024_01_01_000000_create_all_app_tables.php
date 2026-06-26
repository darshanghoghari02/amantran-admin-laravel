<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Creates all application tables using the same id+data JSON schema
 * as the existing Node.js backend. This allows direct compatibility
 * with the existing MySQL database — no data transformation needed.
 */
return new class extends Migration
{
    /**
     * All tables to create (same as Node.js backend).
     */
    private array $tables = [
        'users',
        'app_users',
        'categories',
        'templates',
        'fonts',
        'languages',
        'subscriptions',
        'user_subscriptions',
        'user_purchases',
        'user_drafts',
        'user_cards',
        'user_favorites',
        'guests',
        'transactions',
        'audit_logs',
        'roles',
        'ratings',
        'settings',
        'otp_codes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $table) {
                    $table->string('id', 255)->primary();
                    $table->longText('data');
                });

                // Add a JSON index on the data column for MySQL 5.7+ performance
                // This uses a virtual generated column for common query fields
                DB::statement("ALTER TABLE `{$table}` 
                    ADD INDEX idx_created (((CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.createdAt')) AS DATETIME))))
                ");
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
