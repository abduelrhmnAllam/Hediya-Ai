<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relative extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

  
    public function persons()
    {
        return $this->hasMany(Person::class);
    }
}
