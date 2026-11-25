<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        Gate::authorize('generatePdf', $invoice);

        $invoice->load('items', 'creator');

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        $path = "invoices/{$invoice->invoice_number}.pdf";
        Storage::put($path, $pdf->output());

        if ($invoice->status === 'approved') {
            $invoice->update(['status' => 'generated', 'pdf_path' => $path]);
        }

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
