<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occasion extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'title',
        'date',
        'type',
    ];

    public function person()
    {
        return $this->belongsTo(People::class, 'person_id');
    }
}
