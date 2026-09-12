<?php

namespace App\Http\Controllers;

use App\Models\ShopRegistration;
use App\Models\ApparelCategory;
use App\Models\ShopBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'apparel_categories' => ['nullable', 'array', 'max:20'],
            'apparel_categories.*' => ['string', 'max:100'],
            'branch_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'subscription_plan' => ['required', 'in:basic,pro,premium'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'subscription_price' => ['required', 'numeric', 'in:149,599,899,1788,7188,10788'],
            'landmark_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'proof_documents' => ['required', 'array', 'min:1', 'max:10'],
            'proof_documents.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $landmarkPath = $request->file('landmark_image')->store('shop-registrations/landmarks', 'public');
        $proofPaths = collect($request->file('proof_documents'))
            ->map(fn ($file) => $file->store('shop-registrations/proofs', 'public'))
            ->all();

        $registration = ShopRegistration::create([
            ...collect($validated)->except(['landmark_image', 'proof_documents'])->all(),
            'landmark_image_path' => $landmarkPath,
            'proof_document_paths' => $proofPaths,
        ]);

        ShopBranch::create([
            'shop_registration_id' => $registration->id,
            'branch_name' => $validated['branch_name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => 'pending',
        ]);

        collect($validated['apparel_categories'] ?? [])
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->each(fn (string $name) => ApparelCategory::create([
                'shop_registration_id' => $registration->id,
                'name' => $name,
                'status' => 'pending',
            ]));

        return response()->json([
            'message' => 'Registration submitted for review.',
            'registration_id' => $registration->id,
        ], 201);
    }
}
