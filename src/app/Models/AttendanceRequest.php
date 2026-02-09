<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'target_date',
        'user_id',
        'admin_id',
        'status',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'target_date'  => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function details()
    {
        return $this->hasMany(AttendanceRequestDetail::class);
    }

    public function getRemarkAttribute()
    {
        return optional(
            $this->details->firstWhere('field', 'remark')
        )->new_value;
    }
}
