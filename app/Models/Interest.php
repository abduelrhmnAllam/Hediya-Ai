<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'icon'];

    // ✅ نضيف icon_url تلقائياً عند إرجاع الـ model في الـ API
    protected $appends = ['icon_url'];

    public function people()
    {
        return $this->belongsToMany(People::class, 'person_interest', 'interest_id', 'person_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_interest', 'interest_id', 'user_id');
    }

    // ✅ Getter لرابط الصورة الكامل
    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            // لو الصورة مرفوعة في storage/public/interests
            return asset('storage/' . $this->icon);
        }

        // لو مفيش صورة، رجّع صورة افتراضية
        return asset('images/default-interest.png');
    }
}
