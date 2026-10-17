<?php

namespace App\Http\Middleware\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait EndsSession
{
    /**
     * Sign the user out completely and send them to the login page with a
     * message explaining why.
     */
    protected function endSession(Request $request, string $message): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', $message);
    }
}
