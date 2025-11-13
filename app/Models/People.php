<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;
protected $table = 'people';

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
          'avatar_id',
    ];

    protected $casts = [
        'birthday_date' => 'date',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

            // ✅ علاقة الـAvatar
    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
 public function attachments()
{
    return $this->hasMany(\App\Models\Attachment::class, 'person_id');
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


      /**
     * ✅ Mutator للتعامل مع الصورة (pic)
     */
    public function setPicAttribute($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $this->attributes['pic'] = $value;
            return;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $value)) {
            $image = substr($value, strpos($value, ',') + 1);
            $image = base64_decode($image);
            $imageName = 'person_' . uniqid() . '.png';
            Storage::disk('public')->put('people/' . $imageName, $image);
            $this->attributes['pic'] = '/storage/people/' . $imageName;
            return;
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            $path = $value->store('people', 'public');
            $this->attributes['pic'] = '/storage/' . $path;
            return;
        }

        $this->attributes['pic'] = $value ?: null;
    }

    /**
     * ✅ Accessor يعيد الصورة النهائية كرابط جاهز
     */
    public function getPicAttribute($value)
    {
        if (!$value) return null;
        if (str_starts_with($value, '/storage/')) {
            return asset($value);
        }
        return $value;
    }

    /**
     * ✅ Accessor جديد يعيد avatar بشكل منسّق
     */
    public function getAvatarDataAttribute()
    {
        if (!$this->avatar) {
            return null;
        }

        return [
            'id'    => $this->avatar->id,
            'name'  => $this->avatar->name,
            'image' => $this->avatar->image,
            'gender'=> $this->avatar->gender,
        ];
    }

    /**
     * ✅ Accessor جديد يعيد بيانات الصورة الشخصية pic بشكل واضح
     */
    public function getPicDataAttribute()
    {
        if (!$this->pic) {
            return null;
        }

        return [
            'url'  => $this->pic,
            'type' => 'uploaded',
        ];
    }



}
