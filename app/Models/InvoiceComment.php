<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceComment extends Model
{
    protected $fillable = ['invoice_id', 'user_id', 'body', 'is_issue'];

    protected $casts = ['is_issue' => 'boolean'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
