<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'base_salary',
        'danh_sach_chi_phi',
        'ngan_hang',
        'password',
        'fullname',
        'roles_id',
        'salary',
        'salary_basic',
        'status',
        'attendance_bonus',
        'phone_allowance'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function quoteRequests()
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /**
     * Thiết lập quan hệ với model Quote.
     * Một User có thể tạo nhiều Quote.
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
    public function websites(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Website', 'user_website', 'user_id', 'website_id');
    }
    public function kpiSalaries()
    {
        return $this->hasMany(KpiSalary::class, 'user_id');
    }
    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'user_keyword')
            ->withTimestamps()
            ->withPivot('created_at');
    }
}
