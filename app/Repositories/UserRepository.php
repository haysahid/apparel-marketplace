<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public static function createGuestUser(array $data)
    {
        try {
            if (User::where('email', $data['email'])->exists()) {
                throw new Exception('Email sudah terdaftar.', 409);
            }

            $user = User::create([
                ...$data,
                'role_id' => 8, // guest role
            ]);
            return $user;
        } catch (Exception $e) {
            Log::error('Gagal membuat user tamu: ' . $e);
            throw $e;
        }
    }
}
