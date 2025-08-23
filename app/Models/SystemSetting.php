<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    /* Allowed data types */
    public const TYPE_STRING  = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_JSON    = 'json';

    public const TYPES = [
        self::TYPE_STRING, self::TYPE_INTEGER, self::TYPE_BOOLEAN, self::TYPE_JSON,
    ];

    protected $fillable = [
        'key',
        'value',
        'data_type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected $attributes = [
        'is_public' => false,
        'data_type' => self::TYPE_STRING,
    ];

    /* ---------------- Accessors / Mutators ---------------- */

    // Read "value" in the correct PHP type
    public function getValueAttribute($stored)
    {
        return match ($this->data_type) {
            self::TYPE_INTEGER => $stored === null ? null : (int) $stored,
            self::TYPE_BOOLEAN => (bool) $stored,
            self::TYPE_JSON    => $stored ? json_decode($stored, true) : null,
            default            => $stored, // string
        };
    }

    // Persist "value" as a string/json according to data_type
    public function setValueAttribute($incoming): void
    {
        $this->attributes['value'] = match ($this->data_type) {
            self::TYPE_INTEGER => isset($incoming) ? (string) (int) $incoming : null,
            self::TYPE_BOOLEAN => $incoming ? '1' : '0',
            self::TYPE_JSON    => $incoming === null ? null : json_encode($incoming, JSON_UNESCAPED_UNICODE),
            default            => $incoming, // string
        };
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublic($q)
    {
        return $q->where('is_public', true);
    }

    /* ---------------- Convenience helpers ---------------- */

    /** Quick getter: SystemSetting::get('site.name', 'Default') */
    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row?->value ?? $default;
    }

    /**
     * Quick upsert:
     * SystemSetting::put('site.name', 'My App', SystemSetting::TYPE_STRING, true, 'Public site name');
     */
    public static function put(
        string $key,
        $value,
        ?string $type = null,
        bool $isPublic = false,
        ?string $description = null,
    ): self {
        $setting = static::firstOrNew(['key' => $key]);

        $setting->data_type  = $type ?? $setting->data_type ?? static::inferType($value);
        $setting->is_public  = $isPublic;
        if ($description !== null) {
            $setting->description = $description;
        }
        $setting->value = $value; // triggers mutator
        $setting->save();

        return $setting;
    }

    /** Infer a sensible data_type from a PHP value */
    protected static function inferType($value): string
    {
        return match (true) {
            is_bool($value)             => self::TYPE_BOOLEAN,
            is_int($value)              => self::TYPE_INTEGER,
            is_array($value) || is_object($value) => self::TYPE_JSON,
            default                     => self::TYPE_STRING,
        };
    }
}
