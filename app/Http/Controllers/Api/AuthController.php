<?php

namespace App\Http\Controllers\Api;

use App\ApiModel\ApiUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Business;
use App\User;
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

        $businessId = Business::inRandomOrder()->value('id');
        $userId = User::inRandomOrder()->value('id');

        // Find the last contact_id
        $lastContact = Contact::where('business_id', $businessId)
            ->orderBy('contact_id', 'desc')
            ->first();

        if ($lastContact && preg_match('/C(\d+)/', $lastContact->contact_id, $matches)) {
            $lastNumber = (int) $matches[1]; // e.g., 1 for C0001
            $newNumber = $lastNumber + 1;    // 2
        } else {
            $newNumber = 1; // start from 1 if none exists
        }

        // Keep zero padding (C0001 → C0002)
        $newContactId = 'CO' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Create new contact
        $contact = Contact::create([
            'name' => $validatedData['name'],
            'business_id' => $businessId,
            'type' => 'customer',
            'created_by' => $userId,
            'contact_id' => $newContactId,
            'mobile' => $validatedData['phone'],
        ]);


        Log::info('Contact found or created', ['contact_id' => $contact->id]);

        // Create API user
        $user = ApiUser::firstOrCreate([
            'contact_id' => $contact->id
        ]);
        Log::info('API user found or created', ['api_user_id' => $user->id]);

        // Create JWT token
        $token = JWTAuth::fromUser($user);
        Log::info('JWT token created');

        // Proper cookie for cross-site, mobile + HTTPS support
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

        $phone = preg_replace('/\D+/', '', $credentials['phone']); // remove all non-digits

        $contact = Contact::whereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '+', ''), '-', '') = ?", [$phone])->first();


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
            $token = $request->cookie('token');
            
            if (!$token) {
                Log::warning('No token in user endpoint');
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            // Get user ID from token
            $payload = JWTAuth::getPayload($token);
            $userId = $payload->get('sub');
            
            Log::info('User endpoint - Token decoded', ['user_id' => $userId]);
            
            // Find API user directly
            $apiUser = ApiUser::find($userId);
            
            if (!$apiUser) {
                Log::warning('ApiUser not found', ['user_id' => $userId]);
                return response()->json(['message' => 'User not found'], 401);
            }
            
            Log::info('ApiUser found in user endpoint', [
                'id' => $apiUser->id,
                'contact_id' => $apiUser->contact_id
            ]);

            // Get contact directly
            $contact = Contact::find($apiUser->contact_id);
            
            if (!$contact) {
                Log::error('Contact not found for API user', [
                    'api_user_id' => $apiUser->id,
                    'contact_id' => $apiUser->contact_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Contact record not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $apiUser->id,
                    'profile_url' => $apiUser->profile_url ?? null,
                    'name' => $contact->name ?? null,
                    'mobile' => $contact->mobile ?? null,
                    'reward_points' => [
                        'total' => $contact->total_rp ?? 0,
                        'used' => $contact->total_rp_used ?? 0,
                        'expired' => $contact->total_rp_expired ?? 0,
                        'available' => ($contact->total_rp ?? 0) - ($contact->total_rp_used ?? 0) - ($contact->total_rp_expired ?? 0),
                    ],
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('JWT Exception in user endpoint', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthenticated: ' . $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error('User endpoint exception', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        Log::info('Update profile request received', ['data' => $request->all()]);
        
        try {
            $token = $request->cookie('token');
            
            if (!$token) {
                Log::warning('No token in updateProfile');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication token missing'
                ], 401);
            }
    
            // Get the user ID from token
            $payload = JWTAuth::getPayload($token);
            $userId = $payload->get('sub');
            
            Log::info('Token decoded', ['user_id' => $userId]);
    
            // Find the API user
            $apiUser = ApiUser::find($userId);
            
            if (!$apiUser) {
                Log::error('ApiUser not found', ['user_id' => $userId]);
                return response()->json([
                    'success' => false,
                    'message' => 'User account not found'
                ], 404);
            }
    
            Log::info('ApiUser found', [
                'id' => $apiUser->id,
                'contact_id' => $apiUser->contact_id,
                'profile_url' => $apiUser->profile_url
            ]);
    
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);
            
            // Get the contact using the contact_id
            $contact = Contact::find($apiUser->contact_id);
            
            if (!$contact) {
                Log::error('Contact not found', ['contact_id' => $apiUser->contact_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Contact record not found for this user'
                ], 404);
            }
    
            Log::info('Contact found', [
                'contact_id' => $contact->id,
                'current_name' => $contact->name,
                'current_mobile' => $contact->mobile,
                'business_id' => $contact->business_id
            ]);
    
            // Normalize phone number for duplicate check
            $normalizedPhone = preg_replace('/\D+/', '', $validated['phone']);
            
            // Check if phone is already taken by another contact (excluding current contact)
            $existingContact = Contact::where('id', '!=', $contact->id)
                ->where(function($query) use ($normalizedPhone) {
                    $query->where('mobile', 'LIKE', '%' . $normalizedPhone . '%')
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(mobile, ' ', ''), '+', ''), '-', '') = ?", [$normalizedPhone]);
                })
                ->first();
    
            if ($existingContact) {
                Log::warning('Phone number already taken', [
                    'phone' => $normalizedPhone,
                    'existing_contact_id' => $existingContact->id,
                    'existing_contact_name' => $existingContact->name
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered with another account'
                ], 409);
            }
    
            // Store old values for logging
            $oldName = $contact->name;
            $oldMobile = $contact->mobile;
    
            // Update contact
            $contact->name = $validated['name'];
            $contact->mobile = $validated['phone'];
            $saved = $contact->save();
    
            if (!$saved) {
                Log::error('Failed to save contact', ['contact_id' => $contact->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save changes'
                ], 500);
            }
    
            Log::info('Profile updated successfully', [
                'contact_id' => $contact->id,
                'old_name' => $oldName,
                'new_name' => $contact->name,
                'old_mobile' => $oldMobile,
                'new_mobile' => $contact->mobile
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $apiUser->id,
                    'profile_url' => $apiUser->profile_url ?? null,
                    'name' => $contact->name,
                    'mobile' => $contact->mobile,
                    'reward_points' => [
                        'total' => $contact->total_rp ?? 0,
                        'used' => $contact->total_rp_used ?? 0,
                        'expired' => $contact->total_rp_expired ?? 0,
                        'available' => ($contact->total_rp ?? 0) - ($contact->total_rp_used ?? 0) - ($contact->total_rp_expired ?? 0),
                    ],
                ]
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::error('Token expired', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please log in again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            Log::error('Token invalid', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid session. Please log in again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('JWT Exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            Log::error('Update profile exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
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