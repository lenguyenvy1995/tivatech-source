<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $table = 'salary'; // Tên bảng trong database
    protected $fillable = [
        'user_id',
        'form_salary',
        'worked',
        'bhxh',
        'thuc_lam',
        'diligence',
        'rice_salary',
        'phone_salary',
        'month_salary',
        'base_salary',
        'doanh_thu',
        'hoa_hong',
        'worked_salary',
        'other_cost',
        'total_salary',
        'bonus_salary',
        'receive_salary',
        'salaries_id',
        'em_confirm'
    ];

    /**
     * Mối quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
