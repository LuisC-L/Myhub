<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

class LocalizationHelper
{
    /**
     * Vérifie et applique la localisation au lien.
     *
     * @param array $segments URL Segments
     * @return RedirectResponse|null
     */
    public static function setLocalization(array $segments): RedirectResponse | null
    {

        // Locales et services définis dans config/app.php
        $defaultLocale   = config('app.locale');
        $availableLocales = array_keys(config('app.availableLocales', []));
        $allServices      = config('app.allServicesAvailable', []);
        $currentLocale    = App::getLocale();

        // No segments
        if (empty($segments)) {
//            dump('empty segments');
            $locale = in_array($currentLocale, $availableLocales) ?
                $currentLocale : $defaultLocale;
            App::setLocale($locale);
            return redirect('/' . $locale);
        }

        // First segment is a valid locale
        if (in_array($segments[0], $availableLocales)) {
//            dump('valid locale');
            App::setLocale($segments[0]);
            return null;
        }

        // First segment is a known service
        if (in_array($segments[0], $allServices)) {
//            dump('valid service');
            $locale = in_array($currentLocale, $availableLocales) ?
                $currentLocale : $defaultLocale;
            App::setLocale($locale);
            return redirect('/' . $locale . '/' . implode('/', $segments));
        }

        // If first segment not service or locale, check second segment for service
        if (isset($segments[1]) && in_array($segments[1], $allServices)) {
//            dump('valid service in second segment');
            $invalidSegment = strtolower($segments[0]);
            $segments[0] = in_array($invalidSegment, $availableLocales) ?
                $invalidSegment : $defaultLocale;
            App::setLocale($defaultLocale);
            return redirect('/' . implode('/', $segments));
        }
//        dump('other thing');
        // Not a locale or service → force redirect to /en
        App::setLocale($defaultLocale);
        return redirect('/'.$defaultLocale);
    }
}
