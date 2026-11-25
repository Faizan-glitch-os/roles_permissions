<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'created_by', 'status',
        'customer_name', 'customer_email', 'customer_address',
        'issue_date', 'due_date', 'currency',
        'subtotal', 'tax_rate', 'tax_amount', 'total',
        'notes', 'pdf_path', 'approved_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(InvoiceApproval::class);
    }

    public function comments()
    {
        return $this->hasMany(InvoiceComment::class)->latest();
    }

    public function permissions()
    {
        return $this->hasMany(InvoicePermission::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['approved', 'generated']);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total');
        $this->tax_amount = round($this->subtotal * ($this->tax_rate / 100), 2);
        $this->total = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    public static function nextNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        do {
            $candidate = sprintf('INV-%d-%04d', $year, $count++);
        } while (static::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhereHas('permissions', function ($p) use ($user) {
                    $p->where('user_id', $user->id)
                        ->where('can_view', true)
                        ->where(function ($d) {
                            $d->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
        });
    }
}
