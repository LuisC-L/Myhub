<?php

if(!function_exists('localeUrl')){
    /**
     * Generate a URL with locale.
     *
     * @param  string  $path
     * @param  ?string  $locale
     * @return string
     */
    function localeUrl(string $path, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return '/' . trim($locale . '/' . ltrim($path, '/'), '/');
    }
}

if(!function_exists('localeRoute')){
    /**
     * Generate a URL to a named route with automatic locale parameter.
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function localeRoute($name, $parameters = [], $absolute = true): string
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

