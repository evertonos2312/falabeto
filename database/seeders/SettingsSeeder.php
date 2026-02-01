<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Support\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::query()->first();

        Settings::set('branding.product_name', 'Fala Beto', 'string', 'branding', $admin);
        Settings::set('branding.tagline', 'Seu controle financeiro no WhatsApp', 'string', 'branding', $admin);
        Settings::set('branding.logo_path', null, 'file', 'branding', $admin);
        Settings::set('branding.favicon_path', null, 'file', 'branding', $admin);

        Settings::set('commercial.trial_days_default', 14, 'int', 'commercial', $admin);
        Settings::set('commercial.trial_enabled_default', true, 'bool', 'commercial', $admin);
        Settings::set('commercial.support_whatsapp_e164', '+55', 'string', 'commercial', $admin);
        Settings::set('commercial.support_email', 'suporte@falabeto.test', 'string', 'commercial', $admin);

        Settings::set('security.log_message_body', false, 'bool', 'security', $admin);
        Settings::set('security.message_logs_retention_days', 30, 'int', 'security', $admin);
        Settings::set('security.rate_limit_enabled', true, 'bool', 'security', $admin);
    }
}
