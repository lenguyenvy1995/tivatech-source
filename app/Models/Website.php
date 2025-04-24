<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Website extends Model
{
    protected $table = 'website';

    protected $fillable = ['name', 'user_id', 'status_id'];

    /**
     * Định nghĩa mối quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_website', 'website_id', 'user_id');
    }
    /**
     * Định nghĩa mối quan hệ với Status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo('App\Models\Status');
    }

    /**
     * Định nghĩa mối quan hệ với Campaign
     */
    public function campaign(): HasMany
    {
        return $this->hasMany(Campaign::class, 'website_id', 'id');
    }
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'website_id', 'id');
    }
    public function latestCampaign()
    {
        return $this->hasOne(Campaign::class, 'website_id')->latestOfMany('end');
    }

    /**
     * Định nghĩa mối quan hệ với SetupWeb
     */
    public function setupWebs(): HasMany
    {
        return $this->hasMany('App\Models\SetupWeb');
    }

    /**
     * Phạm vi tùy chỉnh để lấy các website có chiến dịch kết thúc trước 90 ngày
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $currentDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCampaignEndDate($query, Carbon $currentDate)
    {
        return $query->whereHas('campaign', function ($query) use ($currentDate) {
            $query->where('end', '<=', $currentDate->copy()->subDays(90));
        });
    }
}
