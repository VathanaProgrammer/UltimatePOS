<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function getTelegramLink()
    {
        $business_id = auth()->user()->business_id;
        Log::info('Business ID:', ['business_id' => $business_id]);

        $username = BusinessLocation::where('business_id', $business_id)
            ->value('custom_field1');

        $telegram_link = null;

        if ($username) {
            // Remove @ if present
            $username = ltrim($username, '@');

            // Build full URL
            $telegram_link = "https://t.me/{$username}";
        }
        Log::info('Telegram API called');

        return response()->json([
            'telegram_link' => $telegram_link
        ]);
    }
}
