<?php

namespace App\Domains\System\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConfiguration extends Model
{
    protected $table = 'email_configurations';

    protected $fillable = [
        'host', 'port', 'username', 'password', 'encryption',
        'from_address', 'from_name', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer'
    ];
}
