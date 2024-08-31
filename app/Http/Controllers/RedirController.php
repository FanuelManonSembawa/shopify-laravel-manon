<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
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
        $returnUri = 'https://' . $storeUrl . '/admin';

        if ($token != '') {
            $shopData = [
                'domain' => $storeUrl,
                'token' => $token
            ];
    
            \DB::table('tenants')->updateOrInsert(
                ['domain' => $storeUrl],
                $shopData
            );

            return redirect()->to($returnUri)->send();
        } else {
            return redirect()->away('https://' . $storeUrl . '/admin/oauth/error');
        }
    }

    protected function getAccessToken(string $code, $storeUrl)
    {
        $access_token_endpoint = 'https://' . $storeUrl . '/admin/oauth/access_token';

        $body = array(
            "client_id" => "4864b99a0fa1f452c17f26248125e5b2",
            "client_secret" => "5d1c57653eec1970c3e2f19eb29d6a40",
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