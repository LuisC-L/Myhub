<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class LocalizationHelper
{
    public static function setLocalization(array $segments): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|null
    {
        // Handle empty segments array
        if(empty($segments)) {
            $locale = null;
        } elseif(!in_array($segments[0],config('app.publicServices'))){
            $locale = $segments[0] ?? null;
        }else{
            $link = '/' . implode('/',$segments);
            return redirect($link);
        }
        // If locale isn't available or valid, then set to default (local has to always be in the link)
        if(!in_array($locale,array_keys(config('app.availableLocales')))){
            //add default locale to link
            $segments[0] = config('app.locale');
            $link = '/' . implode('/',$segments);
            return redirect($link);
        }
        App::setLocale($locale);
        return null;
    }
}
