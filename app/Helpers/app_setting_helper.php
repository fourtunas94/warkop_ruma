<?php

use App\Models\SettingModel;

if (! function_exists('app_setting')) {
    function app_setting(string $key, ?string $default = null): ?string
    {
        static $settings = null;

        if ($settings === null) {
            try {
                $model = new SettingModel();
                $settings = $model->getAllSettings();
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        return $settings[$key] ?? $default;
    }
}