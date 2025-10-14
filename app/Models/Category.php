<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'slug',
        'parent_id',
    ];

    /**
     * 🔗 العلاقة بين الفئات والأبناء (Tree)
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 🔗 العلاقة مع المنتجات
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
