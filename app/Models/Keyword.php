<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    protected $table = 'keywords';
    protected $primaryKey = 'id';   
    public $timestamps = false;
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_keyword')
                    ->withTimestamps()
                    ->withPivot('created_at');
    }
}
