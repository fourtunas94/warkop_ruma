<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Load all settings into an associative array keyed by setting_key.
     *
     * @return array<string, string|null>
     */
    public static function getAllSettings(): array
    {
        $rows = static::all();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->setting_key] = $row->setting_value;
        }
        return $settings;
    }

    /**
     * Get a single setting value by key, returning $default if not found.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::where('setting_key', $key)->first();
        return $row ? $row->setting_value : $default;
    }

    /**
     * Upsert a setting value by key. Creates the row if it doesn't exist.
     */
    public static function setValue(string $key, ?string $value): bool
    {
        $row = static::where('setting_key', $key)->first();
        if ($row) {
            return (bool) $row->update(['setting_value' => $value]);
        }
        return (bool) static::create(['setting_key' => $key, 'setting_value' => $value]);
    }
}
