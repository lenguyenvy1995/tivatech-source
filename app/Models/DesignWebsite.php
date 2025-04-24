<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignWebsite extends Model
{
    protected $table = 'design_website';

    protected $fillable = [
        'user_id',
        'username_customer',
        'domain_id',
        'hosting_id',
        'prices',
        'registration_date',
        'expiration_date',
        'customer_phone',
        'email',
        'note',
        'status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Quan hệ với bảng Domain
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    /**
     * Quan hệ với bảng Hosting
     */
    public function hosting(): BelongsTo
    {
        return $this->belongsTo(Hosting::class, 'hosting_id');
    }
    public function getOwnershipAttribute(): string
    {
        $domainSupplier = strtolower(trim($this->domain->supplier ?? 'tivatech'));
        $hostingSupplier = strtolower(trim($this->hosting->supplier ?? 'tivatech'));

        $domainLabel = $domainSupplier === 'tivatech' ? 'TivaTech' : ucfirst($domainSupplier);
        $hostingLabel = $hostingSupplier === 'tivatech' ? 'TivaTech' : ucfirst($hostingSupplier);

        if ($domainSupplier === 'tivatech' && $hostingSupplier === 'tivatech') {
            return '<span class="badge badge-success">👑 TivaTech toàn quyền</span>';
        } elseif ($domainSupplier === 'tivatech') {
            return '<span class="badge badge-warning">🧩 Domain TivaTech – Hosting ' . $hostingLabel . '</span>';
        } elseif ($hostingSupplier === 'tivatech') {
            return '<span class="badge badge-warning">🧩 Domain ' . $domainLabel . ' – Hosting TivaTech</span>';
        } else {
            return '<span class="badge badge-secondary">📦 Domain ' . $domainLabel . ' – Hosting ' . $hostingLabel . '</span>';
        }
    }
}
