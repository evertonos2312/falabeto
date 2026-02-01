<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\SettingAudit;
use Illuminate\Support\Facades\Cache;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember('settings.all', now()->addMinutes(30), function () {
            return Setting::query()->get()->keyBy('key');
        });

        $setting = $all->get($key);

        return $setting ? $setting->getValue() : $default;
    }

    public static function set(string $key, mixed $value, string $type, string $group, ?Admin $admin = null): void
    {
        $setting = Setting::query()->key($key)->first();
        $oldValue = $setting ? $setting->getValue() : null;

        if (! $setting) {
            $setting = new Setting([
                'key' => $key,
                'group' => $group,
                'type' => $type,
            ]);
        } else {
            $setting->group = $group;
            $setting->type = $type;
        }

        $setting->setValue($value);
        $setting->updated_by_admin_id = $admin?->id;
        $setting->save();

        Cache::forget('settings.all');

        if ($oldValue !== $setting->getValue()) {
            SettingAudit::query()->create([
                'admin_id' => $admin?->id,
                'key' => $key,
                'old_value' => $oldValue !== null ? json_encode($oldValue) : null,
                'new_value' => $value !== null ? json_encode($value) : null,
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}
