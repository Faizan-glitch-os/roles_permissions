<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceApproval extends Model
{
    protected $fillable = ['invoice_id', 'admin_id', 'status', 'comment', 'acted_at'];

    protected $casts = ['acted_at' => 'datetime'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
