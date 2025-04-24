<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteDomain extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Một quote domain có nhiều yêu cầu báo giá.
     */
    public function quoteRequests()
    {
        return $this->hasMany(QuoteRequest::class)->orderBy('updated_at', 'desc');
    }

    /**
     * Một quote domain có nhiều báo giá thông qua yêu cầu báo giá.
     */
    public function quotes()
    {
        return $this->hasManyThrough(Quote::class, QuoteRequest::class)->orderBy('updated_at', 'desc');
    }
}
