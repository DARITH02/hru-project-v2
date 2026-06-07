<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    use HasFactory;

    protected $table = 'class_groups';
    protected $fillable = ['major_id', 'name', 'year_level'];

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'group_id');
    }

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_class_group', 'class_group_id', 'class_room_id');
    }
}
