<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValues;

class Projects extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            $project->attributeValues()->delete();
        });
    }

    // Many-to-many with Users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // One-to-many with Timesheets
    public function timesheets()
    {
        return $this->hasMany(Timesheets::class);
    }

    // EAV: link to attribute_values table
    public function attributeValues()
    {
        // 'entity_id' in attribute_values references project->id
        return $this->hasMany(AttributeValues::class, 'entity_id');
    }
}
