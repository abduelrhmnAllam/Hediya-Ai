<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedProduct extends Model
{
    protected $fillable = [
        'product_id','feed_id','feed_run_id','feed_offer_id',
        'sku','currency','price','old_price','available','qty_actual','size_count',
        'modified_time','url','brand_page_url','cat1_name','cat2_name','cat3_name','raw'
    ];

    protected $casts = [
        'available' => 'boolean',
        'raw' => 'array',
        'modified_time' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function run()
    {
        return $this->belongsTo(FeedRun::class, 'feed_run_id');
    }

    // 🖼️ Accessor لاستخراج الصورة من الـ raw JSON
    public function getImageUrlAttribute()
    {
        if (isset($this->raw['image']) && filter_var($this->raw['image'], FILTER_VALIDATE_URL)) {
            return $this->raw['image'];
        }

        if (isset($this->raw['pictures'][0]) && filter_var($this->raw['pictures'][0], FILTER_VALIDATE_URL)) {
            return $this->raw['pictures'][0];
        }

        return null;
    }
}
