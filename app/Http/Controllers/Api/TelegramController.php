<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BusinessLocation;

class TelegramController extends Controller
{
    public function getTelegramLink()
    {
        try {
            Log::info('Telegram API called', ['user_id' => auth()->id() ?? 'guest']);
    
            // Get username from DB
            $username = BusinessLocation::value('custom_field1'); // simplest way
    
            // Build full URL
            $telegram_link = $username ? 'https://t.me/' . ltrim($username, '@') : null;
    
            Log::info('Telegram link found', ['link' => $telegram_link]);
    
            return response()->json([
                'telegram_link' => $telegram_link
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage() // optional, for debugging
            ], 500);
        }
    }
}
