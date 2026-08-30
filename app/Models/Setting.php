<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->is_encrypted
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypt = false): void
    {
        $payload = is_string($value) || is_numeric($value) ? (string) $value : json_encode($value);

        $storeValue = $encrypt
            ? Crypt::encryptString($payload)
            : $payload;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storeValue, 'group' => $group, 'is_encrypted' => $encrypt],
        );
    }
}