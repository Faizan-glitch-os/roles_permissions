<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password', 'can_create', 'can_edit', 'can_view', 'can_delete', 'can_approve'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_edit' => 'boolean',
            'can_view' => 'boolean',
            'can_delete' => 'boolean',
            'can_create' => 'boolean',
            'can_approve' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function permissions()
    {
        return $this->hasMany(InvoicePermission::class);
    }

    public function approvals()
    {
        return $this->hasMany(InvoiceApproval::class, 'admin_id');
    }

    public function canCreateInvoice(): bool
    {
        if ($this->can_create) {
            return true;
        } else {
            return false;
        }

        // return $this->permissions()
        //     ->whereNull('invoice_id')
        //     ->where('can_create', true)
        //     ->exists();
    }
}
