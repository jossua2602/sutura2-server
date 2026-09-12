<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(SubscriptionPlan::orderBy('id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        return response()->json(SubscriptionPlan::create($validated), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return response()->json($subscriptionPlan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update($this->validatePlan($request));
        return response()->json($subscriptionPlan->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        return response()->noContent();
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'plan_name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_branches' => ['nullable', 'integer', 'min:0'],
            'perks' => ['nullable', 'array', 'max:20'],
            'perks.*' => ['string', 'max:255'],
        ]);
    }
}
