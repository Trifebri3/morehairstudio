<?php

namespace App\Domains\System\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'group',
        'key',
        'label',
        'value',
        'type',
        'options',
        'description',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the casted value of the setting based on its type.
     */
    public function getCastedValueAttribute()
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
