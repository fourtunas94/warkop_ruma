<?php

if (!function_exists('app_setting')) {
    /**
     * Get a setting value from the settings table.
     */
    function app_setting(string $key, ?string $default = null): ?string
    {
        static $settings = null;

        if ($settings === null) {
            try {
                $settings = \App\Models\Setting::getAllSettings();
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        return $settings[$key] ?? $default;
    }
}
