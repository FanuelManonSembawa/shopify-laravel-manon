<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class InstallController extends Controller
{
    static $shopDomain = null;
    static $client_id = null;

    public function install(\Illuminate\Http\Request $request)
    {
        $redirectUrl = Config::get('shopify.base_url') . '/be-assesment/public/redir';
        $shopifyDomain = $request->shop;
        $scopes = Config::get('shopify.shopify_scopes');
        $authorizeUrl = $this->getAuthorizeUrl($shopifyDomain, $scopes, $redirectUrl);
        
        return redirect()->to($authorizeUrl);
    }

    public function getAuthorizeUrl(string $url, $scope = '', $redirect_url = '') : string
    {
        $client_id = Config::get('shopify.shopify_client_id');
        $this->setShopDomain($url);

        $url = "https://{$this::$shopDomain}/admin/oauth/authorize?client_id=" . $client_id . "&scope=" . urlencode($scope);

        if ($redirect_url != '') {
            $url .= "&redirect_uri=" .urlencode($redirect_url);
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