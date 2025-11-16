<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Contact;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register request received', ['data' => $request->all()]);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Fake OTP
        $otp = rand(100000, 999999);
        Log::info('Generated OTP', ['otp' => $otp]);

        // Find or create contact
        $contact = Contact::firstOrCreate(
            ['mobile' => $validatedData['phone']],
            [
                'name' => $validatedData['name'],
                'business_id' => 1,
                'type' => 'customer',
                'created_by' => 1,
            ]
        );
        Log::info('Contact found or created', ['contact_id' => $contact->id]);

        // Create API user
        $user = ApiUser::firstOrCreate([
            'contact_id' => $contact->id
        ]);
        Log::info('API user found or created', ['api_user_id' => $user->id]);

        // Create JWT token
        $token = JWTAuth::fromUser($user);
        Log::info('JWT token created');

        // ✅ Proper cookie for cross-site, mobile + HTTPS support
        return response()->json([
            'success' => true,
            'message' => 'OTP generated',
            'otp' => $otp
         ])->cookie(
            'token',
            $token,
            60,
            '/',
            '.syspro.asia',  // must match your production domain
            true,             // Secure (for HTTPS)
            true,             // HttpOnly
            false,            // Raw
            'None'            // SameSite=None for cross-site cookie
        );

        //->cookie('token', $token, 60, '/', null, false, true); // old local test cookie
    }

    public function login(Request $request)
    {
        Log::info('Login request received', ['data' => $request->all()]);

        $credentials = $request->validate([
            'phone' => 'required|string',
        ]);

        $contact = Contact::where('mobile', $credentials['phone'])->first();

        if (!$contact) {
            Log::warning('Login failed: contact not found');
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = ApiUser::where('contact_id', $contact->id)->first();

        if (!$user) {
            Log::info('API user not found, creating new one', ['contact_id' => $contact->id]);
            $user = ApiUser::create(['contact_id' => $contact->id]);
        }

        $token = JWTAuth::fromUser($user);
        Log::info('JWT token created for login', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
         ])->cookie(
            'token',
            $token,
            60,
            '/',
            '.syspro.asia',
            true,
            true,
            false,
            'None'
        );
        //->cookie('token', $token, 60, '/', null, false, true); // old local test cookie
    }

    // 🔹 Get user from JWT token
    public function user(Request $request)
    {
        try {
            Log::info('Fetching user info via token');
            $token = $request->cookie('token'); // get token from HttpOnly cookie
            Log::info('Token received from cookie', ['token' => $token ? 'exists' : 'missing']);

            $user = JWTAuth::setToken($token)->toUser();
            $user->load('contact');

            Log::info('User retrieved successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'profile_url' => $user->profile_url,
                    'name' => $user->contact->name ?? null,
                    'mobile' => $user->contact->mobile ?? null,
                ]
            ]);
        } catch (JWTException $e) {
            Log::error('JWT parse error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        // Always accept OTP for testing
        Log::info('OTP verification request', ['otp' => $request->otp]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified',
        ]);
    }

    // 🔹 Logout
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            Log::info('Logout initiated', ['token' => $token ? 'exists' : 'missing']);

            JWTAuth::invalidate($token);
            Log::info('JWT invalidated successfully');

            return response()
                ->json(['message' => 'Logged out successfully', 'success' => true])
                ->cookie('token', '', -1, '/', '.syspro.asia');
        } catch (JWTException $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }
}