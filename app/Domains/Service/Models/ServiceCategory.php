<?php

namespace App\Domains\Service\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
