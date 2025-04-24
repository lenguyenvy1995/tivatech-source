<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'check_in_note',
        'check_out_note',
        'fine',
        'shift_status',
    ];

    // Quan hệ với model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Thêm phương thức để tính toán số giờ làm việc
    public function getTotalHoursAttribute()
    {
        if ($this->check_in && $this->check_out) {
            return $this->check_out->diffInHours($this->check_in);
        }
        return 0;
    }
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
    public function getShiftAttribute()
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }
    
        $checkInHour = $this->check_in->hour;
        $checkOutHour = $this->check_out->hour;
        $dayOfWeek = $this->check_in->dayOfWeek;
    
        // Từ Chủ nhật đến Thứ 6
        if ($dayOfWeek >= Carbon::SUNDAY && $dayOfWeek <= Carbon::FRIDAY) {
            // Check-in từ 8h đến 9h30 và check-out từ 12h đến 15h
            if (($checkInHour >= 8 && $checkInHour <= 9) && ($checkOutHour >= 12 && $checkOutHour <= 15)) {
                return 0.5;
            }
    
            // Check-in từ 8h đến 9h30 và check-out từ 16h30 đến 17h30
            if (($checkInHour >= 8 && $checkInHour <= 9) && ($checkOutHour >= 16 && $checkOutHour <= 17)) {
                return 1;
            }
    
            // Check-in từ 9h30 đến 13h và check-out từ 16h30 đến 17h
            if (($checkInHour >= 9 && $checkInHour <= 13) && ($checkOutHour >= 16 && $checkOutHour <= 17)) {
                return 0.5;
            }
    
            // Check-in từ 13h đến 14h và check-out từ 16h30 đến 17h30
            if (($checkInHour >= 13 && $checkInHour <= 14) && ($checkOutHour >= 16 && $checkOutHour <= 17)) {
                return 0.5;
            }
        }
    
        // Thứ 7
        if ($dayOfWeek == Carbon::SATURDAY) {
            // Check-in từ 8h đến 9h30 và check-out từ 12h đến 14h
            if (($checkInHour >= 8 && $checkInHour <= 9) && ($checkOutHour >= 12 && $checkOutHour <= 14)) {
                return 0.5;
            }
    
            // Check-in từ 8h đến 9h30 và check-out từ 15h đến 17h30
            if (($checkInHour >= 8 && $checkInHour <= 9) && ($checkOutHour >= 15 && $checkOutHour <= 17)) {
                return 1;
            }
    
            // Check-in từ 9h30 đến 13h và check-out từ 15h đến 17h
            if (($checkInHour >= 9 && $checkInHour <= 13) && ($checkOutHour >= 15 && $checkOutHour <= 17)) {
                return 0.5;
            }
    
            // Check-in từ 13h đến 14h và check-out từ 16h30 đến 17h30
            if (($checkInHour >= 13 && $checkInHour <= 14) && ($checkOutHour >= 16 && $checkOutHour <= 17)) {
                return 0;
            }
        }
    
        // Trường hợp không phù hợp với bất kỳ điều kiện nào
        return 0;
    }
    
}
