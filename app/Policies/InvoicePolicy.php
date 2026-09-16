<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /** Determine whether the user can browse invoices. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isAccountant() || $user->isCustomer();
    }

    /** Determine whether the user can view the invoice. */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isAdmin() || $user->isStaff() || $user->isAccountant()) {
            return true;
        }

        // customers can view only their own invoices
        if ($user->isCustomer()) {
            return $invoice->customer?->user_id === $user->id;
        }

        return false;
    }

    /** Determine whether the user can create invoices. */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /** Determine whether the user can update the invoice. */
    public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /** Determine whether the user can send a draft invoice. */
    public function send(User $user, Invoice $invoice): bool
    {
        return $invoice->status === 'draft' && $user->isStaff();
    }

    /** Determine whether the user can delete the invoice. */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
