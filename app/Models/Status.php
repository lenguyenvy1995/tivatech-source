<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;
    protected $table = 'status';

    // Các cột có thể được gán hàng loạt
    protected $fillable = ['name', 'created_at', 'updated_at'];

    /**
     * Quan hệ với Website (một status có thể liên kết với nhiều website)
     */
    public function websites(): HasMany
    {
        return $this->hasMany('App\Models\Website');
    }

    /**
     * Quan hệ với Campaign (một status có thể liên kết với nhiều campaign)
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany('App\Models\Campaign');
    }
}

