<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'إعداد النظام';
    protected static string $modelPluralLabel = 'إعدادات النظام';

    protected $fillable = ['key', 'value', 'group', 'type', 'description'];

    // Helper to get a setting value
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    // Helper to set a setting value
    public static function set(string $key, $value, string $group = 'general', string $type = 'string', ?string $description = null)
    {
        // Auto-detect type if not provided and updating
        if ($type === 'string') {
            if (is_int($value))
                $type = 'integer';
            elseif (is_bool($value))
                $type = 'boolean';
            elseif (is_array($value))
                $type = 'json';
        }

        $storedValue = $value;
        if ($type === 'boolean')
            $storedValue = $value ? '1' : '0';
        if ($type === 'json')
            $storedValue = json_encode($value);

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group,
                'type' => $type,
                'description' => $description
            ]
        );
    }
}
