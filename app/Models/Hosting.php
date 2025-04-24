<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hosting extends Model
{
    protected $table = 'hosting';

    protected $fillable = [
        'supplier',
        'login_link',
        'account',
        'password',
        'registration_date',
        'expiration_date',
        'capacity',
        'status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
        'capacity' => 'integer',
        'status' => 'string',
    ];

    public function websites()
    {
        return $this->hasMany(DesignWebsite::class, 'hosting_id');
    }
}