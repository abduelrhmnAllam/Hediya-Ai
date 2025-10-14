<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'available',
        'category_id',
        'currency_id',
        'name',
        'description',
        'price',
        'old_price',
        'vendor',
        'url',
        'pictures',
        'identifier_exists',
        'modified_time',
    ];

    protected $casts = [
        'available' => 'boolean',
        'pictures' => 'array',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
    ];

    /**
     * 🔗 العلاقة مع الفئة
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
