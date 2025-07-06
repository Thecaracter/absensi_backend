<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_editable',
        'sort_order'
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Get setting value dengan cast otomatis
    public function getCastedValue()
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $this->value;
            case 'integer':
                return (int) $this->value;
            default:
                return $this->value;
        }
    }

    // Static method untuk ambil setting
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->getCastedValue() : $default;
    }

    // Static method untuk set setting
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => $value]);
            return $setting;
        }
        return null;
    }
}