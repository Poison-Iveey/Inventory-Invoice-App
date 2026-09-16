<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\AccountCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $search = request('search');
        $role = request('role');
        $users = User::query()
            ->with('customer:id,user_id,name')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when(in_array($role, ['admin', 'staff', 'accountant', 'customer'], true), fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => UserResource::collection($users),
            'filters' => ['search' => $search, 'role' => $role],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', $this->formOptions());
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                // unusable until the user sets their own via the emailed link below —
                // the admin never chooses or sees a user's password.
                'password' => Hash::make(Str::random(40)),
                'role' => $data['role'],
            ]);
            // email_verified_at is deliberately not mass-assignable; set it explicitly
            // since the admin is vouching for this account.
            $user->forceFill(['email_verified_at' => now()])->save();

            $this->syncCustomerProfile($user, $data['customer_id'] ?? null);

            $token = Password::broker()->createToken($user);
            $user->notify(new AccountCreated($token));
        });

        return redirect()->route('users.index')->with('success', 'User created and invited by email.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', [
            ...$this->formOptions($user),
            'user' => $user->load('customer:id,user_id,name'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Deliberately no password field here: once a user has set their own
        // password, not even the admin can change or see it. If they lose
        // access, they use "Forgot your password" or an admin resends the
        // setup link below (which only lets THEM choose a new one).
        DB::transaction(function () use ($data, $user) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
            ]);
            $this->syncCustomerProfile($user, $data['customer_id'] ?? null);
        });

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    /**
     * Resend the "set your password" link — e.g. the original email was lost.
     * Does not reveal or change the user's existing password if they already set one.
     */
    public function resendInvite(User $user)
    {
        $this->authorize('update', $user);

        $token = Password::broker()->createToken($user);
        $user->notify(new AccountCreated($token));

        return back()->with('success', 'Setup link resent to '.$user->email);
    }

    private function formOptions(?User $user = null): array
    {
        return [
            'customers' => Customer::query()
                ->where(function ($query) use ($user) {
                    $query->whereNull('user_id');

                    if ($user) {
                        $query->orWhere('user_id', $user->id);
                    }
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ];
    }

    private function syncCustomerProfile(User $user, ?int $customerId): void
    {
        $user->customer()->update(['user_id' => null]);

        if ($user->isCustomer() && $customerId) {
            Customer::whereKey($customerId)->update(['user_id' => $user->id]);
        }
    }
}
