<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePermission extends Model
{
    protected $fillable = [
        'user_id', 'invoice_id', 'can_view', 'can_edit', 'can_create',
        'expires_at', 'granted_by',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_edit' => 'boolean',
        'can_create' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isValid(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
