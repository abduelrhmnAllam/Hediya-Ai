<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'address',
        'avatar',
        'verification_code',
        'is_verified',
          'gift_budgets',
       'often_buy',
       'is_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code', // بنخفيه عشان ما يظهرش في الـ API responses
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Relation Example - One user has many persons.
     */
    public function persons()
    {
        return $this->hasMany(People::class);
    }
    public function interests()
{
    return $this->belongsToMany(Interest::class, 'user_interest', 'user_id', 'interest_id');
}

}
