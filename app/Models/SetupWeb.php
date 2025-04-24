<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class SetupWeb extends Model
{
    protected $table = 'setup_webs';
    
    // Các cột có thể gán hàng loạt
    protected $fillable = [
        'website_id', 
        'typecamp_id', 
        'campaign_id', 
        'keyword', 
        'location', 
        'rank', 
        'type_keyword', 
        'time_slot', 
        'budget', 
        'percent', 
        'promotion', 
        'payment', 
        'start', 
        'end', 
        'vat', 
        'status', 
        'note'
    ];
    
    /**
     * Định nghĩa mối quan hệ với model Website
     */
    public function website()
    {
        return $this->belongsTo('App\Models\Website');
    }

    /**
     * Định nghĩa mối quan hệ với model Typecamp
     */
    public function typecamp()
    {
        return $this->belongsTo('App\Models\Typecamp');
    }

    /**
     * Định nghĩa mối quan hệ với model Campaign
     */
    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign');
    }

    /**
     * Định nghĩa mối quan hệ qua trung gian với model Pay thông qua Campaign
     */
    public function pay()
    {
        return $this->hasManyThrough('App\Models\Pay', 'App\Models\Campaign');
    }
}
