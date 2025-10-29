<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClarksstoreCategory extends Model
{
    use HasFactory;

    protected $table = 'clarksstore_categories';

    protected $fillable = [
        'uuid',
        'name',
    ];

    public function products()
    {
        return $this->hasMany(ClarksstoreProduct::class, 'category_id');
    }
}
