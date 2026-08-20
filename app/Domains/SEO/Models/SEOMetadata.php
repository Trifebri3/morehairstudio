<?php

namespace App\Domains\SEO\Models;

use Illuminate\Database\Eloquent\Model;

class SEOMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'path', 'meta_title', 'meta_description', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'schema'
    ];
}
