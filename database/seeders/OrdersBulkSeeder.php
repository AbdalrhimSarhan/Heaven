<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersBulkSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;
        $date = '2026-04-30';

        $ordersCount = 500000;

        /*
         |------------------------------------------------------------
         | Insert batch size
         |------------------------------------------------------------
         | 5000 أو 10000 مناسبين غالبًا.
         | إذا صار عندك Memory error أو MySQL packet error خليه 2000 أو 1000.
         */
        $insertBatchSize = 5000;

        DB::disableQueryLog();

        /*
         |------------------------------------------------------------
         | Optional cleanup
         |------------------------------------------------------------
         | انتبه: هذا يحذف كل الطلبات الموجودة بهذا التاريخ.
         | ممتاز للتجارب حتى لا يتكرر العد.
         */
        DB::table('orders')
            ->whereDate('created_at', $date)
            ->delete();

        $orders = [];

        $this->command->info("Starting insert of {$ordersCount} fake orders...");

        for ($i = 1; $i <= $ordersCount; $i++) {
            $randomHour = str_pad((string) rand(0, 23), 2, '0', STR_PAD_LEFT);
            $randomMinute = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);
            $randomSecond = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);

            $orders[] = [
                'user_id' => $userId,
                'total_price' => rand(500, 5000) / 100, // 5.00 إلى 50.00
                'created_at' => "{$date} {$randomHour}:{$randomMinute}:{$randomSecond}",
                'updated_at' => "{$date} {$randomHour}:{$randomMinute}:{$randomSecond}",
            ];

            if (count($orders) === $insertBatchSize) {
                DB::table('orders')->insert($orders);
                $orders = [];

                $this->command->info("Inserted {$i} / {$ordersCount} orders...");
            }
        }

        if (!empty($orders)) {
            DB::table('orders')->insert($orders);
        }

        $this->command->info("Done. Inserted {$ordersCount} fake orders for date {$date}.");
    }
}