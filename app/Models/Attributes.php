<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValues;

class Attributes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'options',
    ];

    protected $casts = [
        'options' => 'array', // if you store JSON in 'options'
    ];

    public function values()
    {
        return $this->hasMany(AttributeValues::class);
    }
}
