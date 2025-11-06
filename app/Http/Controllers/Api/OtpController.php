<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Twilio\Rest\Client;

class OtpController extends Controller
{
    // Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $otpCode = rand(100000, 999999); // 6-digit OTP
        $expiresAt = Carbon::now()->addMinutes(5);

        Otp::create([
            'phone' => $request->phone,
            'code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send SMS via Twilio
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $twilio->messages->create(
            $request->phone,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => "Your OTP code is $otpCode. It will expire in 5 minutes."
            ]
        );

        return response()->json(['success' => true, 'message' => 'OTP sent']);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $otp = Otp::where('phone', $request->phone)
                  ->where('code', $request->code)
                  ->where('expires_at', '>', now())
                  ->first();

        if (!$otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        // Optional: Delete OTP after successful verification
        $otp->delete();

        return response()->json(['success' => true, 'message' => 'OTP verified']);
    }
}
