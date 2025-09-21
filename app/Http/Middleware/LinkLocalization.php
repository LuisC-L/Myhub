<?php

namespace App\Http\Middleware;

use App\Services\LocalizationHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class LinkLocalization
{
    /*
     * Handle incoming request and change localization with the data from the link
    */
    public function handle(Request $request, Closure $next) : Response
    {
        $linkSegments = $request->segments();
        if($localeResult = LocalizationHelper::setLocalization($linkSegments)){
            return $localeResult;
        }
        return $next($request);
    }
}
