<?php

namespace App\Infrastructure\Support;

use Carbon\Carbon;

class Helpers
{
    /**
     * Format a date to the application's standard format.
     */
    /**
     * Format a date to the application's standard format (e.g., 27 November 2025).
     */
    public static function formatDate($date)
    {
        return $date ? Carbon::parse($date)->locale('ar')->isoFormat('D MMMM YYYY') : '-';
    }

    /**
     * Convert English numbers to Arabic numbers.
     */
    public static function toArabicNum($string)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($western, $arabic, $string);
    }

    /**
     * Generate a random unique string.
     */
    public static function generateUniqueString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
