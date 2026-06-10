<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            $user = User::query()->find($userId);

            SecurityLogger::audit('member_logout', actor: $user, context: [
                'portal' => 'member portal',
            ]);
        }

        return redirect()->route('home')->with('status', 'You have been signed out.');
    }
}
