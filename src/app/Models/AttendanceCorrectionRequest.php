<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'attendance_id',
        'user_id',
        'correction_content',
        'reason',
        'status'
    ];

    public function attendance()
    {
        return $this->belongsTo((Attendance::class));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
