<?php
// app/Models/FeedProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedProfile extends Model
{
    protected $fillable = [
        'feed_code','map_sku','map_title','map_price','map_brand','map_category_path','extra'
    ];

    protected $casts = [
        'extra' => 'array'
    ];
}

