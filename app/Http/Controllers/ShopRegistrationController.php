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
            'shop_name'          => ['required', 'string', 'max:255'],
            'first_name'         => ['required', 'string', 'max:100'],
            'middle_name'        => ['nullable', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'birthday'           => ['required', 'date'],
            'email'              => ['required', 'email', 'max:255'],
            'contact_number'     => ['required', 'string', 'max:30'],
            'address'            => ['required', 'string', 'max:2000'],
            'apparel_categories'   => ['nullable', 'array', 'max:20'],
            'apparel_categories.*' => ['string', 'max:100'],
            'branch_name'        => ['required', 'string', 'max:255'],
            'latitude'           => ['required', 'numeric', 'between:-90,90'],
            'longitude'          => ['required', 'numeric', 'between:-180,180'],
            'subscription_plan'  => ['required', 'in:basic,pro,premium'],
            'billing_cycle'      => ['required', 'in:monthly,yearly'],
            'subscription_price' => ['required', 'numeric', 'in:149,599,899,1788,7188,10788'],
            // Documents
            'landmark_image'     => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'dti_registration'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'tin_id_image'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'brgy_clearance'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'government_id'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'government_id_type' => ['nullable', 'string', 'max:100'],
            // Business permit(s) — still an array
            'proof_documents'    => ['nullable', 'array', 'max:10'],
            'proof_documents.*'  => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            // Payment
            'payment_method'     => ['nullable', 'string', 'max:50'],
            'payment_receipt'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $landmarkPath = $request->file('landmark_image')->store('shop-registrations/landmarks', 'public');

        // Specific documents
        $dtiPath   = $request->hasFile('dti_registration') ? $request->file('dti_registration')->store('shop-registrations/dti', 'public') : null;
        $tinPath   = $request->hasFile('tin_id_image')     ? $request->file('tin_id_image')->store('shop-registrations/tin', 'public')         : null;
        $brgyPath  = $request->hasFile('brgy_clearance')   ? $request->file('brgy_clearance')->store('shop-registrations/brgy', 'public')       : null;
        $govIdPath = $request->hasFile('government_id')    ? $request->file('government_id')->store('shop-registrations/govid', 'public')        : null;

        // Business permit(s)
        $proofPaths = $request->hasFile('proof_documents')
            ? collect($request->file('proof_documents'))
                ->map(fn ($file) => $file->store('shop-registrations/proofs', 'public'))
                ->all()
            : [];

        // Payment receipt
        $receiptPath = $request->hasFile('payment_receipt')
            ? $request->file('payment_receipt')->store('shop-registrations/receipts', 'public')
            : null;

        $registration = ShopRegistration::create([
            'shop_name'             => $validated['shop_name'],
            'first_name'            => $validated['first_name'],
            'middle_name'           => $validated['middle_name'] ?? null,
            'last_name'             => $validated['last_name'],
            'birthday'              => $validated['birthday'],
            'email'                 => $validated['email'],
            'contact_number'        => $validated['contact_number'],
            'address'               => $validated['address'],
            'subscription_plan'     => $validated['subscription_plan'],
            'billing_cycle'         => $validated['billing_cycle'],
            'subscription_price'    => $validated['subscription_price'],
            'landmark_image_path'   => $landmarkPath,
            'proof_document_paths'  => $proofPaths,
            'dti_registration_path' => $dtiPath,
            'tin_id_path'           => $tinPath,
            'brgy_clearance_path'   => $brgyPath,
            'government_id_path'    => $govIdPath,
            'government_id_type'    => $validated['government_id_type'] ?? null,
            'payment_method'        => $validated['payment_method'] ?? null,
            'payment_receipt_path'  => $receiptPath,
        ]);

        ShopBranch::create([
            'shop_registration_id' => $registration->id,
            'branch_name'          => $validated['branch_name'],
            'address'              => $validated['address'],
            'latitude'             => $validated['latitude'],
            'longitude'            => $validated['longitude'],
            'status'               => 'pending',
        ]);

        collect($validated['apparel_categories'] ?? [])
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->each(fn (string $name) => ApparelCategory::create([
                'shop_registration_id' => $registration->id,
                'name'                 => $name,
                'status'               => 'pending',
            ]));

        return response()->json([
            'message'         => 'Registration submitted for review.',
            'registration_id' => $registration->id,
        ], 201);
    }
}

