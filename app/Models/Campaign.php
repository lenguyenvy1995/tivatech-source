<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'website_id',
        'status_id',
        'typecamp_id',
        'user_id',
        'tech_id',
        'top_position',
        'start',
        'end',
        'budgetmonth',
        'percent',
        'promotion',
        'payment',
        'vat',
        'paid',
        'display',
        'keyword_type',
        'device',
        'region',
        'keywords', 
        'notes',
        'created_at',
        'updated_at'
    ];

    // Khai báo các trường date để tự động chuyển thành Carbon instance
    protected $casts = [
        'start' => 'datetime:Y-m-d H:i:s',
        'end' => 'datetime:Y-m-d H:i:s',
    ];
    /**
     * Quan hệ với Website
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');

    }

    /**
     * Quan hệ với Typecamp
     */
    public function typecamp(): BelongsTo
    {
        return $this->belongsTo('App\Models\Typecamp');
    }

    /**
     * Quan hệ với Budget
     */
    public function budgets(): HasMany
    {
        return $this->hasMany('App\Models\Budget');
    }

    /**
     * Quan hệ với Pay
     */
    public function pay(): HasOne
    {
        return $this->hasOne('App\Models\Pay');
    }

    /**
     * Quan hệ với Status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo('App\Models\Status');
    }

    /**
     * Quan hệ với Note
     */
    public function note(): HasMany
    {
        return $this->hasMany('App\Models\Note');
    }

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
    /**
     * Quan hệ với tech 
     */
    public function tech(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Quan hệ với SetupWeb
     */
    public function setupWebs(): HasOne
    {
        return $this->hasOne('App\Models\SetupWeb');
    }
    public function budgetsAll()
{
    return $this->hasMany(\App\Models\Budget::class);
}
}
