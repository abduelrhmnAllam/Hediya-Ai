<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = ['name','slug','website'];

    protected static function booted()
    {
        static::creating(function($b){
            if (empty($b->slug)) $b->slug = Str::slug($b->name);
        });
    }

    public function products(){ return $this->hasMany(Product::class); }
}
