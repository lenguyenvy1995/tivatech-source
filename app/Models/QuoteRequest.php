<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'keywords',
        'top_position',
        'quote_domain_id',
        'region',
        'keyword_type',
        'campaign_type',
        'start_date',
        'end_date',
        'status',
    ];
    protected $casts = [
        'top_position' => 'array',
        'keyword_type' => 'array',
        'campaign_type' => 'array',
    ];

    /**
     * Thiết lập quan hệ với model User.
     * Một QuoteRequest thuộc về một User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thiết lập quan hệ với model Quote.
     * Một QuoteRequest có thể có một Quote.
     */
    public function quote()
    {
        return $this->hasOne(Quote::class);
    }
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function quoteDomain()
    {
        return $this->belongsTo(QuoteDomain::class);
    }
  
}
