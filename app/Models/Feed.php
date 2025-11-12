<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
         protected $fillable = [
             'name','code','type','default_currency','country_code','is_active'
         ];

         public function runs() { return $this->hasMany(FeedRun::class); }


        public function scopeCountry($q, $country)
        {
            return $q->where('country_code', strtoupper($country));
        }

        public function feedProducts()
{
    return $this->hasMany(FeedProduct::class, 'feed_id');
}

}
