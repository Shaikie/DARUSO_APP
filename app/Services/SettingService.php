<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Reads and writes system settings.
 *
 * A single cached key/value lookup is sufficient here and avoids both
 * hard-coded values in controllers and an unnecessary settings framework.
 */
class SettingService
{
    /**
     * All settings keyed by name, with values cast to their declared type.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(config('daruso.settings_cache_key'), function (): array {
            return Setting::all()
                ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->typedValue()])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    /**
     * Persist settings and refresh the cache.
     *
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        foreach ($values as $key => $value) {
            $setting = Setting::firstOrNew(['key' => $key]);

            $setting->value = is_array($value) ? json_encode($value) : (string) $value;
            $setting->type = $this->inferType($value);

            $setting->save();
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(config('daruso.settings_cache_key'));
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}
