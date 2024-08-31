<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    static $shopDomain = null;
    static $client_id = null;

    public function install(\Illuminate\Http\Request $request)
    {
        $redirectUrl = 'https://5996-149-113-66-45.ngrok-free.app/be-assesment/public/redir';
        $shopifyDomain = $request->shop;
        $scopes = 'read_products,write_products';
        $authorizeUrl = $this->getAuthorizeUrl($shopifyDomain, $scopes, $redirectUrl);
        
        return redirect()->to($authorizeUrl);
    }

    public function getAuthorizeUrl(string $url, $scope = '', $redirect_url = '') : string
    {
        $client_id = '4864b99a0fa1f452c17f26248125e5b2';
        $nonce = bin2hex(random_bytes(12));
        $accessMode = 'offline';
        $this->setShopDomain($url);

        $url = "https://{$this::$shopDomain}/admin/oauth/authorize?client_id=" . $client_id . "&scope=" . urlencode($scope);
        
        if ($redirect_url != '') {
            $url .= "&redirect_uri=" .urlencode($redirect_url) . "&state=" . $nonce . "&grant_options[]=" . $accessMode;
        }

        return $url;
    }

    public function setShopDomain(string $urlShop)
    {
        $url = parse_url($urlShop);
        self::$shopDomain = isset($url['host']) ? $url['host'] : self::removeProtocol($urlShop);

        return $this;
    }

    public static function removeProtocol(string $urlShop): string
    {
        $disAllowed = ['http://', 'https://','http//','ftp://','ftps://'];
        foreach ($disAllowed as $dis) {
            if (strpos($urlShop, $dis) === 0) {
                return str_replace($dis, '', $urlShop);
            }
        }

        return $urlShop;
    }

}