<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'supplier_guid',
        'name',
        'slug',
        'parent_id',
        'extra',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        // حسب العلاقة اللي عندك في قاعدة البيانات، غالباً many-to-many
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }
}
