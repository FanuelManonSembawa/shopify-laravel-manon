<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\InstallController;
use App\Models\Products;
use App\Models\Tenants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function push(Request $request)
    {
        $product = $request->product;

        $curl = curl_init();

        $checkShopify = $this->checkProductShopify($product['kode']);

        if (count($checkShopify["data"]["products"]["edges"]) >= 1) {
            $updateProduct = $this->updateProduct($product, $checkShopify["data"]["products"]["edges"][0]['node']['legacyResourceId']);
        } else {
            $createProduct = $this->createProduct($product);
        }
        
    }

    public function getTenants()
    {
        $firstValue = Tenants::first();
        if ($firstValue) {
            return $firstValue;
        } else {
            return 'No users found';
        }
    }

    public function checkProductShopify($sku)
	{
		$tenants = $this->getTenants();
        $shopDomain = $tenants->domain;
        $accessToken = $tenants->token;
		$query = 'query {
            products(first: 1, query: "sku:'.$sku.'") {
              edges {
                node {
                    legacyResourceId
                }
              }
            }
          }';

        $response = Http::withHeaders(['X-Shopify-Access-Token' => $accessToken])
            ->post("https://{$shopDomain}/admin/api/2024-04/graphql.json", [
                'query' => $query
            ]);
		
        return $response->json();
	}

    public function createProduct($request)
    {

        $tenants = $this->getTenants();
        $shopDomain = $tenants->domain;
        $accessToken = $tenants->token;

        $variant = array(
            'price' => $request['harga'],
            'sku' => $request['kode'],
            'title' => $request['nama'],
            'weight' => $request['berat'],
            'weight_unit' => 'kg'
        );

        $image = array(
            'src' => $request['gambar'][0]['image']
        );

		$payload['product'] = array(
            'title' => $request['nama'],
            'body_html' => $request['deskripsi'],
            'status' => $request['status']=='Enable' ? 'active' : 'draft',
            'images' => $image,
            'variants' => $variant
        );

        $response = Http::withHeaders(['X-Shopify-Access-Token' => $accessToken])
            ->post("https://{$shopDomain}/admin/api/2024-04/products.json", $payload);

        $response_create = $response->json();

            $productData = [
                'sku' => $request['kode'],
                'data' => json_encode($payload),
                'shop_product_id' => $response_create['product']['id'],
            ];
    
            \DB::table('products')->updateOrInsert(
                ['sku' => $request['kode']],
                $productData
            );
		
        return $response->json();
    }

    public function updateProduct($request, $id)
    {

        $tenants = $this->getTenants();
        $shopDomain = $tenants->domain;
        $accessToken = $tenants->token;

        $variant = array(
            'price' => $request['harga'],
            'sku' => $request['kode'],
            'title' => $request['nama'],
            'weight' => $request['berat'],
            'weight_unit' => 'kg'
        );

        $image = array(
            'src' => $request['gambar'][0]['image']
        );

		$payload['product'] = array(
            'title' => $request['nama'],
            'body_html' => $request['deskripsi'],
            'status' => $request['status']=='Enable' ? 'active' : 'draft',
            'images' => $image,
            'variants' => $variant
        );

        $response = Http::withHeaders(['X-Shopify-Access-Token' => $accessToken])
            ->put("https://{$shopDomain}/admin/api/2024-04/products/".$id.".json", $payload);

        $response_create = $response->json();

            $productData = [
                'sku' => $request['kode'],
                'data' => json_encode($payload),
                'shop_product_id' => $response_create['product']['id'],
            ];
    
            \DB::table('products')->updateOrInsert(
                ['sku' => $request['kode']],
                $productData
            );
		
        return $response->json();
    }

   
}