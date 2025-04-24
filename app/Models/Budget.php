<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';

    // Khai báo các cột có thể gán hàng loạt
    protected $fillable = ['campaign_id', 'budget', 'date','calu','account','budgetday'];

    // Đảm bảo rằng 'date' là đối tượng Carbon để sử dụng các phương thức định dạng ngày tháng
    protected $dates = ['date'];

    /**
     * Mối quan hệ với Campaign (Budget thuộc về một Campaign)
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
