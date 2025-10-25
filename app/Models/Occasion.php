<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occasion extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'occasion_name_id', // ✅ المفتاح الأجنبي الجديد
        'title',
        'date',
        'type',
    ];

    /**
     * العلاقة مع الشخص (People)
     */
    public function person()
    {
        return $this->belongsTo(People::class, 'person_id');
    }

    /**
     * العلاقة مع جدول أسماء المناسبات (OccasionNames)
     */
    public function occasionName()
    {
        return $this->belongsTo(OccasionName::class, 'occasion_name_id');
    }
}
