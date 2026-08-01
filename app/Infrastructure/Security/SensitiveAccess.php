<?php

namespace App\Infrastructure\Security;

use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class SensitiveAccess
{
    public const SESSION_HASH_KEY = 'sensitive_access_hash';
    public const SESSION_UNTIL_KEY = 'sensitive_access_until';

    public static function getActiveCode(): ?array
    {
        $hash = SystemSetting::get('sensitive.access_code_hash');
        $expiresAt = SystemSetting::get('sensitive.access_code_expires_at');

        if (!$hash || !$expiresAt) {
            return null;
        }

        try {
            $expiresAt = Carbon::parse($expiresAt);
        } catch (\Exception $e) {
            return null;
        }

        return [
            'hash' => (string) $hash,
            'expires_at' => $expiresAt,
        ];
    }

    public static function verifyCode(string $code): bool
    {
        $active = self::getActiveCode();

        if (!$active || now()->greaterThan($active['expires_at'])) {
            return false;
        }

        return hash_equals($active['hash'], hash('sha256', trim($code)));
    }

    public static function markVerified(Request $request): bool
    {
        $active = self::getActiveCode();

        if (!$active || now()->greaterThan($active['expires_at'])) {
            return false;
        }

        if (! $request->hasSession()) {
            return false;
        }

        $request->session()->put(self::SESSION_HASH_KEY, $active['hash']);
        $request->session()->put(self::SESSION_UNTIL_KEY, $active['expires_at']->timestamp);

        return true;
    }

    public static function isVerified(Request $request): bool
    {
        $active = self::getActiveCode();

        if (!$active || now()->greaterThan($active['expires_at'])) {
            return false;
        }

        if (! $request->hasSession()) {
            return false;
        }

        $sessionHash = $request->session()->get(self::SESSION_HASH_KEY);
        $sessionUntil = $request->session()->get(self::SESSION_UNTIL_KEY);

        if (!$sessionHash || !$sessionUntil) {
            return false;
        }

        if (!hash_equals($active['hash'], (string) $sessionHash)) {
            return false;
        }

        return now()->timestamp <= (int) $sessionUntil;
    }
}
