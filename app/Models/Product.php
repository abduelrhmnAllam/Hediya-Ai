<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'brand_id','name','original_name','fingerprint','master_sku',
        'short_description','long_description','material','gender','status','attributes'
    ];

    protected $casts = ['attributes'=>'array'];

    public function brand(){ return $this->belongsTo(Brand::class); }
    public function images(){ return $this->hasMany(ProductImage::class); }
    public function colors(){ return $this->hasMany(ProductColor::class); }
    public function sizes(){ return $this->hasMany(ProductSize::class); }
    public function categories(){ return $this->belongsToMany(Category::class, 'product_categories'); }
    public function feedItems(){ return $this->hasMany(FeedProduct::class); }
}
