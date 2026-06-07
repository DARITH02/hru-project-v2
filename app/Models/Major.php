<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'name', 'code'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function groups()
    {
        return $this->hasMany(ClassGroup::class, 'major_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
