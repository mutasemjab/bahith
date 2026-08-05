<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;

class CheckWebsiteMode
{
    public function handle(Request $request, Closure $next)
    {
        $mode = SiteSetting::raw('website_mode') ?: '1';

        if ($mode === '1') {
            return $next($request);
        }

        return response()->view('front.landing', [], 200);
    }
}
