<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-'.date('Ymd').'-'.str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'sent',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
        ];
    }
}
