<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;

class CheckWebsiteMode
{
    public function handle(Request $request, Closure $next)
    {
        $mode = SiteSetting::raw('website_mode');

        if ($mode !== '0') {
            return $next($request);
        }

        return response()->view('front.landing', [], 200);
    }
}
