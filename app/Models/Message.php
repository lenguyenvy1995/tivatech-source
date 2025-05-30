<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['quote_id', 'user_id', 'content'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function quote() {
        return $this->belongsTo(Quote::class);
    }
}
