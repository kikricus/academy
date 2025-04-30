<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 45; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => '+380' . $faker->numberBetween(100000000, 999999999),
                'position_id' => rand(1, 4),
                'photo' => "https://cdn-icons-png.flaticon.com/512/3781/3781973.png",
                'password' => bcrypt('12341234'),
                'email_verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
