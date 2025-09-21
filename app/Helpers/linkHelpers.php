<?php

if(!function_exists('localeUrl')){
    /**
     * Generate a URL with locale.
     *
     * @param  string  $path
     * @param  bool  $absolute
     * @return string
     */
    function localeUrl(string $path,string $locale = null, bool $absolute = true): string
    {
        if(!isset($locale)){$locale = app()->getLocale();}
        $url = "/{$locale}/".ltrim($path, '/');
        return $absolute ? url($url) : $url;
    }
}

if(!function_exists('route')){
    /**
     * Generate a URL to a named route with automatic locale parameter.
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function localRoute($name, $parameters = [], $absolute = true): string
    {
        // If parameters is not an array, make it one
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        // Add locale parameter if not already present
        if (!isset($parameters['locale'])) {
            $parameters['locale'] = app()->getLocale();
        }

        // Call Laravel's original route helper
        return app('url')->route($name, $parameters, $absolute);
    }
}

