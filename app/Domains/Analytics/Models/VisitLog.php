<?php

namespace App\Domains\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VisitLog extends Model
{
    // Disable timestamps as we use custom created_at timestamp
    public $timestamps = false;

    protected $fillable = [
        'ip_address', 'user_id', 'page_url', 'search_query',
        'location', 'device', 'gender', 'age', 'browser',
        'referrer', 'source_channel'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
