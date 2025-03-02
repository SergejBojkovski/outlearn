<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email already verified'
                ]);
            }
            
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Verification link sent'
            ]);
        }
        
        return back()->with('status', 'verification-link-sent');
    }
}
