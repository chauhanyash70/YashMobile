<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = array(
            'name' => config('app.name'),
            'email' => 'yash@yopmail.com',
            'password' => Hash::make('Password@Yashmobile'),
        );
        if(!User::where('email',$user['email'])->exists()){
            $user = User::create($user);
        }
    }
}
