<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể được gán giá trị hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'quote_request_id',
        'user_id',
        'estimated_cost',
        'details',
        'status',
    ];

    /**
     * Thiết lập quan hệ với model QuoteRequest.
     * Một Quote thuộc về một QuoteRequest.
     */
    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /**
     * Thiết lập quan hệ với model User.
     * Một Quote thuộc về một User (người tạo báo giá).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function quoteDomain()
    {
        return $this->belongsToThrough(QuoteDomain::class, QuoteRequest::class);
    }
    public function messages() {
        return $this->hasMany(Message::class);
    }
}
