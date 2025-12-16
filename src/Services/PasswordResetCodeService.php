<?php

namespace RaDevs\JwtAuth\Services;


use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RaDevs\JwtAuth\Exceptions\PasswordResetException;
use RaDevs\JwtAuth\Exceptions\UserNotFoundException;
use RaDevs\JwtAuth\Models\PasswordResetCode;


class PasswordResetCodeService
{
    private int $codeLength = 8;


    private function generateCode(int $length): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $alphabetLength = strlen($alphabet);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $alphabetLength - 1);
            $result .= $alphabet[$index];
        }
        return $result;
    }


    public function issueCode(string $email, ?string $ipAddress = null, ?string $userAgent = null, int $ttlMinutes = 60): void
    {
        $last = PasswordResetCode::where('email', mb_strtolower($email))->latest('id')->first();
        if ($last && $last->created_at->diffInSeconds(now()) < 60) {
            throw PasswordResetException::rateLimited();
        }


        PasswordResetCode::where('email', mb_strtolower($email))
            ->active()
            ->update(['expires_at' => now()]);


        $rawCode = $this->generateCode($this->codeLength);
        $normalized = strtoupper($rawCode);


        PasswordResetCode::create([
            'email' => mb_strtolower($email),
            'code_hash' => Hash::make($normalized),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes($ttlMinutes),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);


        $userModel = config('ra-jwt-auth.classes.user_model');
        $user = $userModel::where('email', mb_strtolower($email))->first();
        if ($user) {
            $notification = config('ra-jwt-auth.classes.notification');
            $user->notify(new $notification($normalized, $ttlMinutes));
        }
    }


    public function verifyCode(string $email, string $code): bool
    {
        $record = PasswordResetCode::where('email', mb_strtolower($email))
            ->active()
            ->latest('id')
            ->first();


        if (!$record) return false;
        if ($record->attempts >= $record->max_attempts) return false;


        $isMatch = Hash::check(strtoupper($code), $record->code_hash);
        if (!$isMatch) $record->increment('attempts');


        return $isMatch;
    }


    public function resetPassword(string $email, string $code, string $newPassword): void
    {
        DB::transaction(function () use ($email, $code, $newPassword) {
            $record = PasswordResetCode::where('email', mb_strtolower($email))
                ->active()
                ->lockForUpdate()
                ->latest('id')
                ->first();


            if (!$record) {
                throw PasswordResetException::codeExpired();
            }
            if ($record->attempts >= $record->max_attempts) {
                throw PasswordResetException::tooManyAttempts();
            }
            if (!Hash::check(strtoupper($code), $record->code_hash)) {
                $record->increment('attempts');
                throw PasswordResetException::codeInvalid();
            }


            $record->update(['used_at' => now()]);


            $userModel = config('ra-jwt-auth.classes.user_model');
            $user = $userModel::where('email', mb_strtolower($email))->first();
            if (!$user) {
                throw new UserNotFoundException();
            }


            $user->forceFill(['password' => Hash::make($newPassword)])->save();
            event(new PasswordReset($user));
        });
    }
}