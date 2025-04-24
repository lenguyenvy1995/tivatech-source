<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCustomer extends Model
{
    use HasFactory;

    protected $table = 'data_customers';

    protected $fillable = [
        'user_id', 'domain', 'phone', 'keywords', 'feedback', 'quote_status'
    ];

    // Kết nối với bảng users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
