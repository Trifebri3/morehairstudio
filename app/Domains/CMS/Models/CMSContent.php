<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CMSContent extends Model
{
    protected $table = 'cms_contents';

    protected $fillable = ['key', 'value'];
}
