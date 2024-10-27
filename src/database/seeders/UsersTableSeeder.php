<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * @return void
     * Run the database seeds.
     * Creating the two default testing users.
     */
    public function run():void
    {
        User::create([
            'name' => 'Alice',
            'email' => 'alice@mail.com',
            'password' => bcrypt('123456'),
        ]);

        User::create([
            'name' => 'Bob',
            'email' => 'bob@mail.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
