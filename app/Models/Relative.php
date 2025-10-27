<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Relative extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image'];

    // ✅ إرجاع رابط الصورة الكامل
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/default-relative.png');
    }
     public function persons()
    {
        return $this->hasMany(Person::class);
    }
}
