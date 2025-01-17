<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    public function saveToken(Request $request)
    {
        $token = $request->token;
        // Save token to database for the authenticated user
        $user = User::find(Auth::user()->id);
        $user->update(['fcm_token' => $token]);

        return response()->json(['success' => true]);
    }

    public function sendNotification(Request $request)
    {
        // Retrieve tokens from database
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        // Firebase credentials and setup
        $firebaseCredentialsPath = public_path('json/fir.json');
        $factory = (new Factory)->withServiceAccount($firebaseCredentialsPath);
        $messaging = $factory->createMessaging();

        // Create the notification
        $notification = Notification::create(
            $request->title,
            $request->body
        );

        // Create the CloudMessage
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($request->data ?? []);

        try {
            // Send the notification to multiple tokens
            $response = $messaging->sendMulticast($message, $tokens);

            // Parse the response
            $successCount = $response->successes()->count();
            $failureCount = $response->failures()->count();
            $failures = $response->failures()->map(function ($failure) {
                return $failure->error()->getMessage();
            });

            return response()->json([
                'success' => true,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'failures' => $failures,
            ]);
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
