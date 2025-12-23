<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;

class TestAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Asset::updateOrCreate(
            ['user_id' => 1, 'symbol' => 'BTC'],
            ['amount' => 1.5, 'locked_amount' => 0]
        );

        Asset::updateOrCreate(
            ['user_id' => 1, 'symbol' => 'ETH'],
            ['amount' => 10, 'locked_amount' => 0]
        );

        Asset::updateOrCreate(
            ['user_id' => 2, 'symbol' => 'BTC'],
            ['amount' => 2, 'locked_amount' => 0]
        );

        Asset::updateOrCreate(
            ['user_id' => 2, 'symbol' => 'ETH'],
            ['amount' => 5, 'locked_amount' => 0]
        );
    }
}
