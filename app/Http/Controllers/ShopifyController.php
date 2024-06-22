<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Redirect;

class ShopifyController extends Controller
{
    public function index()
    {
        $client_id = '4864b99a0fa1f452c17f26248125e5b2';
        $ngrok_url = 'https://5de3-139-195-238-53.ngrok-free.app';
        $scopes = 'read_products,write_products,read_orders,write_orders';
        $redirect_uri = $ngrok_url.'/be-assesment/public/token';
        $nonce = bin2hex(random_bytes(12));
        $access_mode = 'per-user';
        $shop = $_GET['shop'];

        $oauth_url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $client_id . '&scope=' . $scopes . '&redirect_uri=' . urlencode($redirect_uri) . '&state=' . $nonce . '&grant_options[]=' . $access_mode;

        echo 'test';

        return redirect($oauth_url);
    }

    public function token()
    {
        $api_key = '4864b99a0fa1f452c17f26248125e5b2';
        $secret_key = '5d1c57653eec1970c3e2f19eb29d6a40';
        $parameters = $_GET;
        $hmac = $parameters['hmac'];
        $shop_url = $parameters['shop'];
        $parameters = array_diff_key($parameters, array('hmac' => ''));
        ksort($parameters);

        $new_hmac = hash_hmac('sha256', http_build_query($parameters), $secret_key);
        if (hash_equals($hmac, $new_hmac)) {
            $access_token_endpoint = 'https://' . $shop_url . '/admin/oauth/access_token';

            $body = array(
                "client_id" => $api_key,
                "client_secret" => $secret_key,
                "code" => $parameters['code']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $access_token_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, count($body));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));

            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['access_token'])) {
                $shopData = [
                    'shop_url' => $shop_url,
                    'access_token' => $response['access_token'],
                    'hmac' => $hmac,
                    'created_at' => NOW()
                ];
        
                \DB::table('shops')->updateOrInsert(
                    ['shop_url' => $shop_url],
                    $shopData
                );
                
                dd($shop_url);
                // return redirect()->away('https://' . $shop_url . '/admin/apps');
                // return redirect('https://' . $shop_url . '/admin/apps');
            } else {
                return redirect()->away('https://' . $shop_url . '/admin/oauth/error');
            }
        }
        else {
            echo 'hmac not true';
        }

    }
}
