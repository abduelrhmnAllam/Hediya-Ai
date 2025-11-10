<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccasionName extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
            'background_color',
    'image_background',
    ];

    public function occasions()
    {
        return $this->hasMany(Occasion::class, 'occasion_name_id');
    }
    protected $appends = ['image_background_url'];

public function getImageBackgroundUrlAttribute()
{
    if(!$this->image_background) return null;
    return asset('storage/'.$this->image_background);
}

}
