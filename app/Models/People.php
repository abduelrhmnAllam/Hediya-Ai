<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'pic',
        'birthday_date',
        'relative_id',
        'age',
        'gender',
        'region',
        'city',
        'address',
    ];

    protected $casts = [
        'birthday_date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function relative()
    {
        return $this->belongsTo(Relative::class);
    }


   public function interests()
{
    return $this->belongsToMany(Interest::class, 'person_interest', 'person_id', 'interest_id');
}
public function occasions()
{
    return $this->hasMany(Occasion::class, 'person_id');
}

}
