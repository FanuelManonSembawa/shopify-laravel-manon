<?php

return [

    'shopify_client_id' => env('SHOPIFY_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Shopify Client Secret
    |--------------------------------------------------------------------------
    |
    | The client secret from the private app.
    |
    */

    'shopify_client_secret' => env('SHOPIFY_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Shopify Domain
    |--------------------------------------------------------------------------
    |
    | The shopify domain for your shop.
    |
    */

    'shopify_domain' => env('SHOPIFY_DOMAIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Shopify Scopes
    |--------------------------------------------------------------------------
    |
    | Access scopes for shopify app.
    |
    */

    'shopify_scopes' => env('SHOPIFY_SCOPES', 'read_products,write_products'),

    /*
    |--------------------------------------------------------------------------
    | App Home URL
    |--------------------------------------------------------------------------
    |
    | This URL that will be loaded when users opens the app from admin panel.
    |
    */
    'shopify_app_home_url' => env('SHOPIFY_APP_HOME_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session key
    |--------------------------------------------------------------------------
    |
    | The session key used to save session data after app load.
    |
     */
    'base_url' => env('BASE_URL', '/'),

];