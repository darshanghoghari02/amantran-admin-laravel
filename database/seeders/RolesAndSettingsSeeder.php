<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toISOString();

        // Seed default roles (mirrors Node.js DEFAULT_ROLES)
        $roles = [
            ['id' => 'super_admin', 'data' => ['name' => 'Super Administrator', 'description' => 'Full unrestricted access to all system features', 'permissions' => ['*'], 'isDefault' => true, 'isActive' => true, 'createdAt' => $now]],
            ['id' => 'admin',       'data' => ['name' => 'Administrator',        'description' => 'Full access to templates, categories, users, analytics', 'permissions' => ['dashboard.view','templates.view','templates.create','templates.edit','templates.delete','categories.view','categories.create','categories.edit','categories.delete','fonts.view','fonts.create','fonts.edit','fonts.delete','languages.view','languages.create','languages.edit','languages.delete','subscriptions.view','subscriptions.edit','subscriptions.manage_pricing','subscriptions.activate','subscriptions.deactivate','users.view','users.edit','roles.view','audit-logs.view','settings.view'], 'isDefault' => true, 'isActive' => true, 'createdAt' => $now]],
            ['id' => 'editor',      'data' => ['name' => 'Template Editor',      'description' => 'Can create, edit, and manage templates and categories', 'permissions' => ['dashboard.view','templates.view','templates.create','templates.edit','categories.view','fonts.view','fonts.create','languages.view'], 'isDefault' => true, 'isActive' => true, 'createdAt' => $now]],
            ['id' => 'viewer',      'data' => ['name' => 'Read-Only Viewer',     'description' => 'Can only view templates and categories', 'permissions' => ['dashboard.view','templates.view','categories.view','fonts.view','languages.view'], 'isDefault' => true, 'isActive' => true, 'createdAt' => $now]],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['id' => $role['id'], 'data' => json_encode($role['data'], JSON_UNESCAPED_UNICODE)]
            );
        }

        // Seed default settings
        $settingsExists = DB::table('settings')->where('id', 'system_config')->exists();
        if (!$settingsExists) {
            DB::table('settings')->insert([
                'id'   => 'system_config',
                'data' => json_encode([
                    'appName'               => 'Amantran Invitation App CMS',
                    'supportEmail'          => 'support@amantran.com',
                    'maintenanceMode'       => false,
                    'defaultUserRole'       => 'user',
                    'allowSelfRegistration' => true,
                    'createdAt'             => $now,
                    'updatedAt'             => $now,
                ], JSON_UNESCAPED_UNICODE)
            ]);
        }

        // Seed default subscription plans
        $plans = [
            ['id' => 'monthly', 'data' => ['name' => 'Monthly Premium', 'price' => 99, 'description' => 'Access all monthly premium templates.', 'isActive' => true, 'includedCategories' => [], 'includedTemplateIds' => [], 'durationType' => 'monthly', 'durationDays' => 30, 'createdAt' => $now]],
            ['id' => 'yearly',  'data' => ['name' => 'Yearly Premium',  'price' => 499, 'description' => 'Access all premium templates including yearly exclusives.', 'isActive' => true, 'includedCategories' => [], 'includedTemplateIds' => [], 'durationType' => 'yearly', 'durationDays' => 365, 'createdAt' => $now]],
        ];

        foreach ($plans as $plan) {
            DB::table('subscriptions')->updateOrInsert(
                ['id' => $plan['id']],
                ['id' => $plan['id'], 'data' => json_encode($plan['data'], JSON_UNESCAPED_UNICODE)]
            );
        }
    }
}
