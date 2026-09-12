<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE shop_subscriptions subscriptions
            INNER JOIN shops shops ON shops.id = subscriptions.shop_id
            INNER JOIN users users ON users.id = shops.owner_id
            INNER JOIN shop_registrations registrations
                ON registrations.email = users.email
                AND registrations.shop_name = shops.shop_name
                AND registrations.status = 'approved'
            SET
                subscriptions.billing_cycle = registrations.billing_cycle,
                subscriptions.amount = registrations.subscription_price,
                subscriptions.end_date = CASE
                    WHEN registrations.billing_cycle = 'yearly'
                        THEN DATE_ADD(subscriptions.start_date, INTERVAL 1 YEAR)
                    ELSE DATE_ADD(subscriptions.start_date, INTERVAL 1 MONTH)
                END
        SQL);
    }

    public function down(): void
    {
        // Billing values are historical data and should not be reverted.
    }
};
