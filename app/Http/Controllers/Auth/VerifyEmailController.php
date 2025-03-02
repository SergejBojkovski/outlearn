<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => 'User not found'], 404)
                : redirect()->route('login')->with('error', 'User not found');
        }

        // Check if URL is valid and not expired
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => 'Invalid verification link'], 403)
                : redirect()->route('login')->with('error', 'Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            // Log the user in if they aren't already
            if (!Auth::check()) {
                Auth::login($user);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email already verified'
                ]);
            }
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Log the user in if they aren't already
        if (!Auth::check()) {
            Auth::login($user);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
