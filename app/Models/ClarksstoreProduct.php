<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClarksstoreProduct extends Model
{
    use HasFactory;

    protected $table = 'clarksstore_products';

    protected $fillable = [
        'offer_id',
        'category_id',
        'vendor',
        'name',
        'description',
        'price',
        'old_price',
        'currency',
        'color',
        'size',
        'gender',
        'picture',
        'url',
        'available',
        'modified_at',
    ];

    protected $casts = [
        'available' => 'boolean',
        'modified_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ClarksstoreCategory::class, 'category_id');
    }
}
