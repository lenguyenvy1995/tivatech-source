<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = [
        'user_id',
        'campaign_id',
        'note',
        'created_at',
        'updated_at'
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Quan hệ với Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo('App\Models\Campaign');
    }
}
