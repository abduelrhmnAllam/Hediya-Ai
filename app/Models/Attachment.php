<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'file',
        'product_name',
        'product_brand',
        'price',
        'store_name',
        'note',
    ];


    // ✅ Mutator للتعامل مع الصورة أو الملف
    public function setFileAttribute($value)
    {
        if (!$value) {
            $this->attributes['file'] = null;
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $this->attributes['file'] = $value;
            return;
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            $path = $value->store('attachments', 'public');
            $this->attributes['file'] = '/storage/' . $path;
            return;
        }

        if (preg_match('/^data:.*;base64,/', $value)) {
            $fileData = substr($value, strpos($value, ',') + 1);
            $fileName = 'attach_' . uniqid() . '.png';
            Storage::disk('public')->put('attachments/' . $fileName, base64_decode($fileData));
            $this->attributes['file'] = '/storage/attachments/' . $fileName;
            return;
        }

        $this->attributes['file'] = $value;
    }

    public function getFileAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    // ✅ العلاقة مع الشخص
    public function person()
    {
        return $this->belongsTo(People::class);
    }
}
