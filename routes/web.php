<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\InvoiceApprovalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', fn () => redirect()->route('invoices.index'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('invoices.index'))->name('dashboard');

    Route::resource('invoices', InvoiceController::class)->except(['destroy']);
    Route::post('invoices/{invoice}/submit', [InvoiceController::class, 'submit'])->name('invoices.submit');
    Route::get('invoices/{invoice}/pdf', InvoicePdfController::class)->name('invoices.pdf');

    Route::post('invoices/{invoice}/approve', [InvoiceApprovalController::class, 'approve'])
        ->middleware('role:admin')->name('invoices.approve');
    Route::post('invoices/{invoice}/reject', [InvoiceApprovalController::class, 'reject'])
        ->middleware('role:admin')->name('invoices.reject');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        Route::delete('invoices/{invoice}/permissions/{user}',
            [PermissionController::class, 'revokeForInvoice'])
            ->name('invoices.permissions.revoke');
    });
});

require __DIR__.'/auth.php';
