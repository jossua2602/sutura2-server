<?php

namespace App\Http\Controllers\Admin;

use App\Models\Shop;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\ShopSubscription;
use App\Models\ShopRegistration;
use App\Models\SubscriptionPlan;
use App\Models\ApparelCategory;
use App\Models\ShopBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingRegistrations = ShopRegistration::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get(['id', 'shop_name', 'first_name', 'last_name', 'created_at']);

        return response()->json([
            'total_users' => User::count(),
            'total_shops' => Shop::count(),
            'active_subscriptions' => ShopSubscription::where('status', 'active')->count(),
            'pending_verifications' => Shop::where('verification_status', 'pending')->count(),
            'pending_registrations' => ShopRegistration::where('status', 'pending')->count(),
            'notifications' => $pendingRegistrations->map(fn (ShopRegistration $registration) => [
                'id' => $registration->id,
                'title' => 'New shop registration',
                'message' => "{$registration->shop_name} submitted by {$registration->first_name} {$registration->last_name}",
                'created_at' => $registration->created_at,
            ])->values(),
        ]);
    }

    public function pendingShops(Request $request)
    {
        return response()->json([
            'shops' => Shop::where('verification_status', 'pending')->get(),
            'registrations' => ShopRegistration::where('status', 'pending')
                ->latest()
                ->get([
                    'id', 'shop_name', 'first_name', 'middle_name', 'last_name',
                    'email', 'contact_number', 'address', 'subscription_plan',
                    'billing_cycle', 'subscription_price', 'landmark_image_path',
                    'proof_document_paths', 'created_at',
                ])->map(function (ShopRegistration $registration) use ($request) {
                    return [
                        ...$registration->toArray(),
                        'landmark_image_url' => $registration->landmark_image_path
                            ? $request->getSchemeAndHttpHost().'/storage/'.$registration->landmark_image_path
                            : null,
                        'proof_document_urls' => collect($registration->proof_document_paths ?? [])
                            ->map(fn (string $path) => $request->getSchemeAndHttpHost().'/storage/'.$path)
                            ->values(),
                    ];
                })->values(),
        ]);
    }

    public function shopDirectory(Request $request)
    {
        $query = Shop::query()
            ->leftJoin('users', 'users.id', '=', 'shops.owner_id')
            ->leftJoin('shop_subscriptions', function ($join) {
                $join->on('shop_subscriptions.shop_id', '=', 'shops.id')
                    ->where('shop_subscriptions.status', 'active');
            })
            ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'shop_subscriptions.plan_id')
            ->select([
                'shops.id', 'shops.shop_name', 'shops.address', 'shops.verification_status',
                'shops.account_status', 'shops.visibility', 'shops.created_at',
                'users.name as owner_name', 'users.email as owner_email',
                'subscription_plans.plan_name', 'shop_subscriptions.start_date',
                'shop_subscriptions.end_date',
            ])
            ->orderByDesc('shops.created_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('shops.shop_name', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('verification_status')) {
            $query->where('shops.verification_status', $request->verification_status);
        }
        if ($request->filled('account_status')) {
            $query->where('shops.account_status', $request->account_status);
        }
        if ($request->filled('visibility')) {
            $query->where('shops.visibility', $request->visibility);
        }

        return response()->json($query->get());
    }

    public function updateShopStatus(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'account_status' => ['sometimes', 'in:active,suspended'],
            'visibility' => ['sometimes', 'in:public,hidden,featured'],
        ]);

        $shop->update($validated);
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'SHOP_STATUS_UPDATED',
            'description' => "Updated shop status: {$shop->shop_name}",
        ]);

        return response()->json($shop->fresh());
    }

    public function approveShop(Request $request, Shop $shop)
    {
        $shop->update(['verification_status' => 'verified', 'visibility' => 'public']);

        ShopSubscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $request->input('plan_id', 1), // default to Basic
            'start_date' => now(),
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'SHOP_APPROVED',
            'description' => "Approved shop: {$shop->shop_name}",
        ]);

        return response()->json(['message' => 'Shop approved']);
    }

    public function rejectShop(Request $request, Shop $shop)
    {
        $request->validate(['reason' => 'required|string']);

        $shop->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'SHOP_REJECTED',
            'description' => "Rejected shop: {$shop->shop_name} — {$request->reason}",
        ]);

        return response()->json(['message' => 'Shop rejected']);
    }

    public function approveRegistration(Request $request, ShopRegistration $registration)
    {
        if ($registration->status !== 'pending') {
            return response()->json(['message' => 'This registration has already been reviewed.'], 422);
        }

        $result = DB::transaction(function () use ($request, $registration) {
            $owner = User::firstOrCreate(
                ['email' => $registration->email],
                [
                    'name' => trim("{$registration->first_name} {$registration->middle_name} {$registration->last_name}"),
                    'password' => Str::random(40),
                    'role' => 'shop_owner',
                ]
            );
            if ($owner->role !== 'shop_owner') {
                $owner->update(['role' => 'shop_owner']);
            }

            $shop = Shop::create([
                'owner_id' => $owner->id,
                'shop_name' => $registration->shop_name,
                'address' => $registration->address,
                'verification_status' => 'verified',
                'visibility' => 'public',
            ]);

            $plan = SubscriptionPlan::whereRaw('LOWER(plan_name) = ?', [$registration->subscription_plan])->first();
            if (!$plan) {
                abort(422, "Subscription plan '{$registration->subscription_plan}' was not found.");
            }

            ShopSubscription::create([
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $registration->billing_cycle,
                'amount' => $registration->subscription_price,
                'start_date' => now(),
                'end_date' => $registration->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'status' => 'active',
            ]);

            $registration->update(['status' => 'approved']);
            ApparelCategory::where('shop_registration_id', $registration->id)->update(['shop_id' => $shop->id]);
            ShopBranch::where('shop_registration_id', $registration->id)->update(['shop_id' => $shop->id]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action_type' => 'SHOP_REGISTRATION_APPROVED',
                'description' => "Approved shop registration: {$registration->shop_name}",
            ]);

            return $shop;
        });

        return response()->json(['message' => 'Shop registration approved', 'shop_id' => $result->id]);
    }

    public function rejectRegistration(Request $request, ShopRegistration $registration)
    {
        $request->validate(['reason' => 'required|string|max:2000']);

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_type' => 'SHOP_REGISTRATION_REJECTED',
            'description' => "Rejected shop registration: {$registration->shop_name} — {$request->reason}",
        ]);

        return response()->json(['message' => 'Shop registration rejected']);
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user:id,name,email')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('action_type', 'like', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->boolean('export')) {
            $logs = $query->get();
            return response()->streamDownload(function () use ($logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Date', 'User', 'Email', 'Action', 'Description']);
                foreach ($logs as $log) {
                    fputcsv($handle, [$log->id, $log->created_at, $log->user?->name, $log->user?->email, $log->action_type, $log->description]);
                }
                fclose($handle);
            }, 'sutura-audit-logs.csv', ['Content-Type' => 'text/csv']);
        }

        return response()->json($query->paginate(50));
    }

    public function activeShops()
    {
        return response()->json(ShopSubscription::query()
            ->where('shop_subscriptions.status', 'active')
            ->join('shops', 'shops.id', '=', 'shop_subscriptions.shop_id')
            ->join('subscription_plans', 'subscription_plans.id', '=', 'shop_subscriptions.plan_id')
            ->join('users', 'users.id', '=', 'shops.owner_id')
            ->orderBy('shop_subscriptions.end_date')
            ->get([
                'shop_subscriptions.id as subscription_id',
                'shop_subscriptions.start_date',
                'shop_subscriptions.end_date',
                'shops.id as shop_id',
                'shops.shop_name',
                'shops.address',
                'shops.visibility',
                'users.name as owner_name',
                'subscription_plans.plan_name',
                'subscription_plans.price',
            ]));
    }

    public function toggleShopVisibility(Request $request, Shop $shop)
    {
        $visible = $request->boolean('visible');
        $shop->update(['visibility' => $visible ? 'public' : 'hidden']);

        return response()->json([
            'message' => $visible ? 'Shop is now visible to customers.' : 'Shop is now hidden from customers.',
            'visible' => $visible,
        ]);
    }

    public function subscriptionReport()
    {
        $active = ShopSubscription::where('status', 'active');
        $rangeStart = Carbon::now()->startOfMonth()->subMonths(5);
        $rangeEnd = Carbon::now()->endOfMonth();
        $monthly = ShopSubscription::whereBetween('start_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as subscriptions, COALESCE(SUM(amount), 0) as revenue")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $months = collect(range(0, 5))->map(function (int $offset) use ($rangeStart, $monthly) {
            $month = $rangeStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $row = $monthly->get($key);
            return [
                'label' => $month->format('M Y'),
                'subscriptions' => (int) ($row->subscriptions ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
            ];
        });

        $byPlan = ShopSubscription::join('subscription_plans', 'subscription_plans.id', '=', 'shop_subscriptions.plan_id')
            ->selectRaw('subscription_plans.plan_name as plan, COUNT(*) as subscriptions, COALESCE(SUM(shop_subscriptions.amount), 0) as revenue')
            ->groupBy('subscription_plans.plan_name')
            ->orderBy('subscription_plans.id')
            ->get()
            ->map(fn ($row) => ['plan' => $row->plan, 'subscriptions' => (int) $row->subscriptions, 'revenue' => (float) $row->revenue]);

        $registrationCounts = ShopRegistration::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $approved = (int) ($registrationCounts->get('approved', 0));
        $rejected = (int) ($registrationCounts->get('rejected', 0));
        $reviewed = $approved + $rejected;
        $registrationTrend = ShopRegistration::whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, status, COUNT(*) as total")
            ->groupBy('month', 'status')
            ->get()
            ->groupBy('month');
        $approvalMonths = collect(range(0, 5))->map(function (int $offset) use ($rangeStart, $registrationTrend) {
            $month = $rangeStart->copy()->addMonths($offset);
            $rows = $registrationTrend->get($month->format('Y-m'), collect())->keyBy('status');
            $approved = (int) ($rows->get('approved')->total ?? 0);
            $rejected = (int) ($rows->get('rejected')->total ?? 0);
            $reviewed = $approved + $rejected;
            return [
                'label' => $month->format('M Y'),
                'approved' => $approved,
                'rejected' => $rejected,
                'approval_rate' => $reviewed > 0 ? round(($approved / $reviewed) * 100, 1) : 0,
            ];
        });

        return response()->json([
            'active_subscriptions' => (int) $active->count(),
            'total_revenue' => (float) ShopSubscription::sum('amount'),
            'monthly_revenue' => $months,
            'by_plan' => $byPlan,
            'registrations' => [
                'total' => (int) $registrationCounts->sum(),
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => (int) ($registrationCounts->get('pending', 0)),
                'approval_rate' => $reviewed > 0 ? round(($approved / $reviewed) * 100, 1) : 0,
                'trend' => $approvalMonths,
            ],
            'generated_at' => now(),
        ]);
    }
}
