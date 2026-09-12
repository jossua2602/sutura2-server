<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApparelCategory;
use Illuminate\Http\Request;

class ApparelCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ApparelCategory::query()->with(['registration:id,shop_name,email', 'shop:id,shop_name']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return response()->json($query->latest()->get());
    }

    public function update(Request $request, ApparelCategory $category)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'rejected' && blank($validated['rejection_reason'] ?? null)) {
            return response()->json(['message' => 'A rejection reason is required.'], 422);
        }

        $category->update($validated);
        return response()->json($category->fresh()->load(['registration:id,shop_name,email', 'shop:id,shop_name']));
    }
}
