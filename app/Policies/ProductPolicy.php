<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isAccountant() || $user->isCustomer();
    }

    /** The full detail page (SKU, exact stock) is not for customers — they only get the stripped catalog list. */
    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isAccountant();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isStaff();
    }
}
