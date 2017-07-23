<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Api user',
            'email' => 'api',
            'password' => bcrypt('q1w2e3'),
        ]);
        DB::table('users')->insert([
            'name' => 'test user',
            'email' => 'test@test.com',
            'password' => bcrypt('q1w2e3'),
        ]);
    }
}
