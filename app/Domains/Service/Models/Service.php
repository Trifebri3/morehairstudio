<?php

namespace App\Domains\Service\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;

class Service extends Model
{
    protected $fillable = [
        'service_category_id', 'name', 'slug', 'description',
        'default_price', 'default_duration', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_price' => 'decimal:2',
        'default_duration' => 'integer',
    ];

    protected $appends = [
        'formatted_description_html',
        'price',
        'duration',
    ];

    public function getPriceAttribute()
    {
        return $this->default_price;
    }

    public function getDurationAttribute()
    {
        return $this->default_duration;
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function outlets()
    {
        return $this->belongsToMany(Outlet::class, 'outlet_services')
                    ->withPivot('price', 'duration', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get clean, beautifully formatted description HTML.
     */
    public function getFormattedDescriptionHtmlAttribute(): string
    {
        $desc = $this->description;
        if (empty($desc)) {
            return '';
        }

        // If it does not contain HTML tags, wrap in simple paragraph
        if (!preg_match('/<[a-z][\s\S]*>/i', $desc)) {
            return '<p>' . nl2br(e($desc)) . '</p>';
        }

        // Remove empty paragraph tags like <p><br></p>, <p><br/></p>, <p>&nbsp;</p>
        $desc = preg_replace('/<p>\s*(<br\s*\/?>|&nbsp;|\s*)*<\/p>/i', '', $desc);

        // Normalize bullet points written as <p>• ...</p>
        if (str_contains($desc, '•')) {
            $desc = preg_replace_callback('/(<p>\s*•\s*.*?<\/p>\s*)+/s', function ($matches) {
                $raw = $matches[0];
                // Extract individual bullet items
                preg_match_all('/<p>\s*•\s*(.*?)<\/p>/s', $raw, $itemMatches);
                if (!empty($itemMatches[1])) {
                    $listHtml = '<ul class="service-included-list">';
                    foreach ($itemMatches[1] as $item) {
                        $listHtml .= '<li>' . trim($item) . '</li>';
                    }
                    $listHtml .= '</ul>';
                    return $listHtml;
                }
                return $raw;
            }, $desc);
        }

        // Add class to standard <ul> tags
        $desc = str_replace('<ul>', '<ul class="service-included-list">', $desc);

        return $desc;
    }
}
