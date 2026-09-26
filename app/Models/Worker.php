<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Worker extends Model
{
    protected $fillable = [
        'name',
        'email',
        'department',
        'phone',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function latestAttendance(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class)->latestOfMany('recorded_at');
    }
}
