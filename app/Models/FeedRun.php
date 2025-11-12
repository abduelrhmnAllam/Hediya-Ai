<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedRun extends Model
{
    protected $fillable = ['feed_id','file_name','file_hash','imported_at','meta'];

    protected $casts = [
        'meta' => 'array',
        'imported_at' => 'datetime',
    ];

    public function feed(){ return $this->belongsTo(Feed::class); }
    public function items(){ return $this->hasMany(FeedProduct::class); }
}
