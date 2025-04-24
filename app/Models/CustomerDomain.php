<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDomain extends Model
{
    protected $table = 'customer_domains'; // nếu table không giống tên model

    protected $fillable = [
        'user_id',
        'username_customer',
        'domain_id',
        'hosting_id',
        'prices',
        'registration_date',
        'expiration_date',
        'customer_phone',
        'email',
        'status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
        'prices' => 'integer',
        'status' => 'string',
    ];

    // Quan hệ với bảng domain
    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    // Quan hệ với bảng hosting
    public function hosting()
    {
        return $this->belongsTo(Hosting::class, 'hosting_id');
    }
}