<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleAdsConfig extends Model
{
    use HasFactory;
    protected $table = 'google_ads_configs';
    // protected $guarded = []; // hoặc chỉ định các trường cụ thể
}
