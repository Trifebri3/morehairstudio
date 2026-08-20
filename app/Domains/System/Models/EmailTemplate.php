<?php

namespace App\Domains\System\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = ['name', 'subject', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'json',
        'is_active' => 'boolean'
    ];
}
