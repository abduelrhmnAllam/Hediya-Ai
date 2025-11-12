<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $feedPrices = $this->feedItems
            ->whereNotNull('price')
            ->sortBy('price')
            ->values();

        return [
            'id'    => $this->id,
            'name'  => $this->name,

            'brand' => [
                'id'   => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ],

            'categories' => $this->categories->map(fn($c)=>[
                'id'=>$c->id,
                'name'=>$c->name,
                'slug'=>$c->slug
            ]),

            'pricing' => [
                'min' => $feedPrices->min('price'),
                'max' => $feedPrices->max('price'),
                'feeds' => $feedPrices->map(function($fp){
                    return [
                        'feed_id' => $fp->feed_id,
                        'price'   => $fp->price,
                        'currency'=> $fp->currency,
                        'url'     => $fp->url,
                    ];
                })
            ],

            'media' => [
                'images' => $this->images->pluck('url'),
            ],

            'attributes' => [
                'colors' => $this->colors->pluck('color'),
                'sizes'  => $this->sizes->pluck('size'),
            ],
        ];
    }
}
