<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domain';

    protected $fillable = [
        'domain',
        'supplier',
        'login_link',
        'account',
        'password',
        'registration_date',
        'expiration_date',
        'status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
        'status' => 'string',
    ];
    public function websites()
    {
        return $this->hasMany(DesignWebsite::class, 'domain_id');
    }

}