<?php

use App\Support\Settings;

if (! function_exists('settings')) {
    function settings(string $key, mixed $default = null): mixed
    {
        return Settings::get($key, $default);
    }
}
