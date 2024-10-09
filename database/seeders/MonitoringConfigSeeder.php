<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonitoringConfigSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('monitoring_configs')->insert([
            [
                'order' => 1,
                'color' => 'bg-green-400',
                'status' => 'Healthy',
                'description' => 'Request received in less than 3 days',
                'days' => 3,
                'condition' => '<'
            ],
            [
                'order' => 2,
                'color' => 'bg-yellow-400',
                'status' => 'Warning',
                'description' => 'No requests for 3 days',
                'days' => 3,
                'condition' => '>='
            ],
            [
                'order' => 3,
                'color' => 'bg-orange-400',
                'status' => 'Critical',
                'description' => 'No requests for 5 days',
                'days' => 5,
                'condition' => '>='
            ],
            [
                'order' => 4,
                'color' => 'bg-red-400',
                'status' => 'Fatal',
                'description' => 'No requests for 10 days',
                'days' => 10,
                'condition' => '>='
            ],
        ]);
    }
}
