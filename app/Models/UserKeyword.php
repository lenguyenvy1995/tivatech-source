<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKeyword extends Model
{
    use HasFactory;
    protected $table = 'user_keyword';
    protected $fillable = ['user_id', 'keyword_id'];
}
