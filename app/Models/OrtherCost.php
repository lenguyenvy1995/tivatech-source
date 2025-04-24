<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrtherCost extends Model
{
    use HasFactory;

    protected $table = 'orther_cost'; // Tên bảng trong database
    protected $fillable = ['user_id', 'khach_hang', 'dich_vu', 'doanh_thu', 'hoa_hong', 'date'];
    public $timestamps = false;

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
