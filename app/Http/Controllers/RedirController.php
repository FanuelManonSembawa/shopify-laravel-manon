<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use App\Models\Store;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\InstallController;

class RedirController extends Controller
{
    public function redir(\Illuminate\Http\Request $request)
    {
        $storeUrl = $request->shop;
        $code = $request->code;

        $installController = new InstallController;
        $installController->setShopDomain($storeUrl);
        $token = $this->getAccessToken($code,$storeUrl);

        if ($token != '') {
            $shopData = [
                'domain' => $storeUrl,
                'token' => $token
            ];
    
            \DB::table('tenants')->updateOrInsert(
                ['domain' => $storeUrl],
                $shopData
            );

            dd($storeUrl);
            return redirect()->to('https://' . $storeUrl . '/admin');
        } else {
            return redirect()->away('https://' . $storeUrl . '/admin/oauth/error');
        }
    }

    public function getAccessToken(string $code, $storeUrl)
    {
        $access_token_endpoint = 'https://' . $storeUrl . '/admin/oauth/access_token';

        $body = array(
            "client_id" => Config::get('shopify.shopify_client_id'),
            "client_secret" => Config::get('shopify.shopify_client_secret'),
            "code" => $code
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $access_token_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($body));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);

        return $response['access_token'] ?? '';
    }
}