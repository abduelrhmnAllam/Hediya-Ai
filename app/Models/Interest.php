<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = ['title'];


   public function people()
{
    return $this->belongsToMany(People::class, 'person_interest', 'interest_id', 'person_id');
}

}
