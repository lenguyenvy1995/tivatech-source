<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiSalary extends Model
{
    use HasFactory;

    protected $table = 'kpi_salary'; // Tên bảng
    protected $fillable = ['user_id', 'doanh_thu', 'hoa_hong'];

    // Mối quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
