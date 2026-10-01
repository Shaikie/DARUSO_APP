<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A configurable application setting.
 *
 * Values are typed on the `type` column so booleans and numbers round-trip
 * correctly, and are cached so controllers never hard-code them.
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Cast the raw text value into its declared type.
     */
    public function typedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOL),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array', 'json' => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }
}
