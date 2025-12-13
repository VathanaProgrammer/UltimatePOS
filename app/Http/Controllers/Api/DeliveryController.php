<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Services\TelegramService;
use SebastianBergmann\Type\TrueType;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.delivery']);
    }

    public function getOrders()
    {
        try {
            $deliveryId = auth()->id(); // current delivery user ID

            $orders = DB::table('transactions as t')
                ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->select(
                    DB::raw("COALESCE(c.first_name, c.last_name, c.name) as customer_name"),
                    'c.mobile as phone',
                    DB::raw("
                            CASE
                                WHEN c.shipping_address IS NOT NULL AND c.shipping_address != '' 
                                    THEN c.shipping_address
                                WHEN c.address_line_1 IS NOT NULL AND c.address_line_1 != '' 
                                    AND c.address_line_2 IS NOT NULL AND c.address_line_2 != ''
                                    THEN CONCAT(c.address_line_1, ', ', c.address_line_2)
                                WHEN c.address_line_1 IS NOT NULL AND c.address_line_1 != ''
                                    THEN c.address_line_1
                                ELSE NULL
                            END AS address
                        "),
                    't.invoice_no as order_no',
                    't.final_total as cod_amount',
                    't.shipping_status',
                    't.id as transaction_id'
                )
                ->where('t.delivery_person', $deliveryId)
                ->whereNotIn('t.shipping_status', ['cancelled', 'delivered'])
                ->orderBy('t.transaction_date', 'asc')
                ->get();

            // Loop through orders and attach comments with user info
            $orders->transform(function ($order) {
                $comments = DB::table('delivery_comments as dc')
                    ->join('users as u', 'u.id', '=', 'dc.user_id')
                    ->select(
                        'dc.id',
                        'dc.comment',
                        'dc.created_at',
                        'u.id as user_id',
                        'u.first_name',
                        'u.last_name',
                        'u.username',
                        'u.image_url as profile_pic'
                    )
                    ->where('dc.invoice_no', $order->order_no)
                    ->orderBy('dc.created_at', 'asc')
                    ->get();

                $order->comments = $comments; // attach comments array
                return $order;
            });

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function decryptQr(Request $request)
    {
        $qrText = $request->input('qr_text');

        try {
            $transaction_id = (int) $qrText;

            $transaction = DB::table('transactions')
                ->where('id', $transaction_id)
                ->first();

            if (!$transaction) {
                \Log::info("error", ["error" => "transaction not found!"]);
                return response()->json([
                    'success' => 0,
                    'msg' => 'Transaction_not_found'
                ]);
            }

            // --- CHECK STATUS ---
            $status = strtolower($transaction->shipping_status ?? '');

            if ($status === 'delivered') {
                \Log::info("error", ["error" => "This_order_is_already_delivered"]);
                return response()->json([
                    'success' => 0,
                    'msg' => 'This_order_is_already_delivered'
                ]);
            }

            if ($status === 'cancelled') {
                \Log::info("error", ["error" => "This_order_is_cancelled"]);
                return response()->json([
                    'success' => 0,
                    'msg' => 'This_order_is_cancelled'
                ]);
            }
            // --------------------

            $contact = DB::table('contacts')
                ->where('id', $transaction->contact_id)
                ->first();

            $data = [
                'id' => $transaction->id,
                'order_no' => $transaction->invoice_no,
                'customer_name' => $contact ? $contact->name : 'Unknown',
                'address' => $transaction->shipping_address ?? ($contact ? $contact->address_line_1 : ''),
                'cod_amount' => $transaction->final_total,
            ];

            return response()->json([
                'success' => 1,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error("catch" . $e);
            return response()->json([
                'success' => 0,
                'msg' => 'Invalid_QR_code'
            ]);
        }
    }


    public function assignDeliveryPerson(Request $request)
    {
        \Log::info('error', ["error" => $request->all()]);
        $transactionId = $request->input('transaction_id');
        $deliveryPersonId = auth()->id();

        if (!$transactionId || !$deliveryPersonId) {
            \Log::info('error', ["error" => 'Transaction ID and Delivery Person ID are required']);
            return response()->json([
                'success' => 0,
                'msg' => 'Transaction ID and Delivery Person ID are required'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Fetch transaction first
            $transaction = DB::table('transactions')->where('invoice_no', $transactionId)->first();

            if (!$transaction) {
                DB::rollBack();
                \Log::info('error', ["error" => 'Transaction_not_found']);
                return response()->json([
                    'success' => 0,
                    'msg' => 'Transaction_not_found'
                ], 404);
            }

            // 2. Check if already assigned
            if ($transaction->delivery_person !== null && $transaction->delivery_person != '') {
                \Log::info('error', ["error" => 'Delivery_person_already_assigned']);
                return response()->json([
                    'success' => 0,
                    'msg' => 'Delivery_person_already_assigned',
                    'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
                ]);
            }

            // 3. Update
            DB::table('transactions')
                ->where('invoice_no', $transactionId)
                ->update([
                    'delivery_person' => $deliveryPersonId,
                    'shipping_status' => 'pick-up',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => 'Delivery person assigned successfully',
                'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json([
                'success' => 0,
                'msg' => 'Error while assigning delivery person',
                'data' => 'transaction id: ' . $transactionId . ' and delivery person: ' . $deliveryPersonId
            ], 500);
        }
    }

    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            $groupId = '-5083476540'; // your Telegram group ID
            $newId = "-1003265141698";
            $invoice = addcslashes($request->invoice_no, '_*[]()~`>#+-=|{}.!/');

            $text =
                "📦 *Drop Off Completed*\n\n" .
                "👤 *Customer:* {$request->name}\n" .
                "📞 *Phone:* {$request->phone}\n" .
                "📍 *Address:* {$request->address_detail}\n" .
                "🧭 Lat: {$request->latitude}\n" .
                "# invoice_no: {$invoice}\n" .
                "🧭 Lon: {$request->longitude}\n" .
                "User id from token: " . auth()->user()->id . "\n" .
                "User id from token: " . auth()->id();

            // Fetch transaction
            $transaction = DB::table("transactions")
                ->where("invoice_no", $request->invoice_no)
                ->first();

            if (!$transaction) {
                return [
                    'success' => 0,
                    'msg' => 'Transaction not found!'
                ];
            }

            // Insert customer data into c_customers
            $customerId = DB::table('c_customers')->insertGetId([
                'name' => $request->name,
                'phone' => $request->phone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address_detail' => $request->address_detail,
                'collector_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert each photo into c_photos
            if ($request->hasFile('photos')) {
                $photoPaths = [];
                foreach ($request->file('photos') as $photo) {
                    $destinationPath = public_path('dropoff_photos');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0775, true);
                    }

                    $filename = time() . '_' . $photo->getClientOriginalName();
                    $photo->move($destinationPath, $filename);

                    // Save to DB
                    DB::table('c_photos')->insert([
                        'customer_id' => $customerId,
                        'image_url' => 'dropoff_photos/' . $filename,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Add path for Telegram
                    $photoPaths[] = [
                        'path' => $destinationPath . '/' . $filename,
                        'name' => $filename
                    ];
                }
                $user = auth()->user();
                $fullName = $user->first_name;
                if (!empty($user->last_name)) {
                    $fullName .= ' ' . $user->last_name;
                }
                $username = $user->username ?? '';
                // $userInfoLine = "(testing) ដឹកជញ្ជូនដោយ: usertesting";

                $userInfoLine = "ដឹកជញ្ជូនដោយ: {$fullName}" . ($username ? " ({$username})" : "");

                // Send all photos to Telegram
                TelegramService::sendImagesToGroup($photoPaths, $userInfoLine);
            }
            // Update transaction as delivered
            DB::table('transactions')
                ->where('invoice_no', $request->invoice_no)
                ->update([
                    'shipping_status' => 'delivered',
                    'delivery_person' => $transaction->delivery_person ?: auth()->id(),
                    'updated_at' => now()
                ]);

            DB::commit();

            return [
                'success' => 1,
                'msg' => 'Saved, marked as delivered, inserted customer & photos, and sent to Telegram',
                'data' => $text
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return [
                'success' => 0,
                'msg' => 'Failed to save or update',
                'error' => $e->getMessage()
            ];
        }
    }

    public function save_comment(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'invoice_no' => 'required|string',  // string because invoice_no may contain letters
            'comment' => 'required|string|max:225',
        ]);

        try {
            DB::beginTransaction();

            // Create a new comment
            DB::table('delivery_comments')->insert([
                'invoice_no' => $validated['invoice_no'],
                'user_id' => auth()->id(),      // get current logged-in user ID
                'comment' => $validated['comment'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Comment saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg' => 'Failed to save comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_profile_pic(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'profile_pic' => 'required|image|max:5048', // max ~5MB
            ]);

            $user = $request->user();

            // Check if file exists
            $file = $request->file('profile_pic');
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }

            // Ensure the folder exists in public/profile_pics
            $destinationPath = public_path('profile_pics');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0775, true);
            }

            // Create unique filename
            $filename = time() . '_' . $file->getClientOriginalName();

            // Move file to public/profile_pics
            $file->move($destinationPath, $filename);

            // Update user's profile picture
            $user->image_url = 'profile_pics/' . $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'image_url' => asset('profile_pics/' . $filename),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Other errors
            \Log::error('Failed to update profile pic', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture. Please try again.',
            ], 500);
        }
    }
}