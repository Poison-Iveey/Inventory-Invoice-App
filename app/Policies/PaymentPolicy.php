<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /** Determine whether the user can browse payments. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isAccountant() || $user->isCustomer();
    }

    /** Determine whether the user can view the payment. */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin() || $user->isStaff() || $user->isAccountant()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $payment->invoice?->customer?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can submit a payment — a customer paying for
     * their own invoice online, or an accountant recording a payment a
     * customer made outside the system (cash, bank transfer, etc).
     */
    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAccountant();
    }

    /** Determine whether the user can approve or reject the payment. */
    public function review(User $user, Payment $payment): bool
    {
        return $payment->status === 'pending' && $user->isAccountant();
    }
}
