<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopBranch;
use Illuminate\Http\Request;

class ShopBranchController extends Controller
{
    public function index(Request $request)
    {
        $query = ShopBranch::query()->with(['registration:id,shop_name,email', 'shop:id,shop_name']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('branch_name', 'like', "%{$request->search}%")
                    ->orWhere('address', 'like', "%{$request->search}%");
            });
        }
        return response()->json($query->latest()->get());
    }

    public function update(Request $request, ShopBranch $branch)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'rejected' && blank($validated['rejection_reason'] ?? null)) {
            return response()->json(['message' => 'A rejection reason is required.'], 422);
        }

        $branch->update($validated);
        return response()->json($branch->fresh()->load(['registration:id,shop_name,email', 'shop:id,shop_name']));
    }
}
