<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->select(['id', 'name', 'email', 'role', 'status', 'created_at']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return response()->json($query->latest()->get());
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === $request->user()->id && ($request->filled('status') && $request->status !== 'active' || $request->filled('role') && $request->role !== 'admin')) {
            return response()->json(['message' => 'You cannot suspend or change your own admin account.'], 422);
        }

        $validated = $request->validate([
            'role' => ['sometimes', 'in:admin,shop_owner,staff,customer'],
            'status' => ['sometimes', 'in:active,suspended'],
        ]);

        $user->update($validated);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'status', 'created_at']));
    }
}
