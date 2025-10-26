<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'clock_in',
        'clock_out', 
        'status'
    ];

    protected $casts = [
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
