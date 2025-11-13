<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'gender', 'is_default'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset($this->image);
    }
}
