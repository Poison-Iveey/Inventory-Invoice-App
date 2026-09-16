<?php

use App\Models\Invoice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Invoice::query()
        ->where('status', 'sent')
        ->whereDate('due_date', '<', today())
        ->update(['status' => 'overdue']);
})->daily()->name('mark-overdue-invoices');
