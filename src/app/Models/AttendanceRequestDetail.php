<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'field',
        'old_value',
        'new_value',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
