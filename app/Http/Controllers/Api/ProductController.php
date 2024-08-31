<?php
namespace App\Http\Controllers\Api;

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
        $shopifyProductCreate = true;

        try {
            $checkProduct = $this->checkProduct($request->product['kode']);

            if (!$checkProduct) {

                $product_id = \DB::table('products')
                ->insertGetId([
                    'sku' => $request->product['kode']
                ]);

                $checkProduct = Products::find($product_id);

            } 

            if ($checkProduct->shop_product_id == null) {
                $checkShopify = $this->checkProductShopify($request->product['kode']);

                if (isset($checkShopify["data"]["products"]["edges"]) && !empty($checkShopify['data']['products']['edges'])) {
                    $updateProduct = $this->updateProduct($request->product, $checkShopify["data"]["products"]["edges"][0]['node']['legacyResourceId']);
                    $shopifyProductCreate = false;
                } else {
                    $updateProduct = $this->createProduct($request->product);
                }
            }else {
                $updateProduct = $this->updateProduct($request->product, $checkProduct->shop_product_id);
                $shopifyProductCreate = false;
            }

            if ($updateProduct->status() == 200 || $updateProduct->status() == 201) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product Successfully ' . ($shopifyProductCreate ? 'Created' : 'Updated' ),
                    'product' => $updateProduct->json()
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Product Failed to ' . ($shopifyProductCreate ? 'Create' : 'Update')
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    protected function checkProduct($kode)
    {
        $product = Products::where('sku', $kode)->first();
        return $product;
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

    protected function checkProductShopify($sku)
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

    protected function createProduct($request)
    {

        $tenants = $this->getTenants();
        $shopDomain = $tenants->domain;
        $accessToken = $tenants->token;

        $variants = [
            'price' => (float)$request['harga'],
            'sku' => $request['kode'],
            'title' => $request['nama'],
            'weight' => (float)$request['berat'],
        ];

        foreach ($request['gambar'] as $image) {
            $images[] = [
                'src' => $image['image']
            ];
        }

        $payloads['product'] = [
            'title' => $request['nama'],
            'body_html' => $request['deskripsi'],
            'status' => $request['status'] == 'Enable' ? 'active' : 'draft',
            'images' => $images,
            'variants' => [$variants],
            'weight_unit' => 'kg'
        ];


        $response = Http::withHeaders(['X-Shopify-Access-Token' => $accessToken])
            ->post("https://{$shopDomain}/admin/api/2024-04/products.json", $payloads);

        $response_create = $response->json();

        $productData = [
            'sku' => $request['kode'],
            'data' => json_encode($payloads),
            'shop_product_id' => $response_create['product']['id'],
        ];
    
        \DB::table('products')->updateOrInsert(
            ['sku' => $request['kode']],
            $productData
        );
		
        return $response;
    }

    protected function updateProduct($request, $id)
    {

        $tenants = $this->getTenants();
        $shopDomain = $tenants->domain;
        $accessToken = $tenants->token;

        $variants = [
            'price' => (float)$request['harga'],
            'sku' => $request['kode'],
            'title' => $request['nama'],
            'weight' => (float)$request['berat'],
        ];

        foreach ($request['gambar'] as $image) {
            $images[] = [
                'src' => $image['image']
            ];
        }

        $payloads['product'] = [
            'title' => $request['nama'],
            'body_html' => $request['deskripsi'],
            'status' => $request['status'] == 'Enable' ? 'active' : 'draft',
            'images' => $images,
            'variants' => [$variants],
            'weight_unit' => 'kg'
        ];

        $response = Http::withHeaders(['X-Shopify-Access-Token' => $accessToken])
            ->put("https://{$shopDomain}/admin/api/2024-04/products/".$id.".json", $payloads);

        $response_create = $response->json();

        $productData = [
            'sku' => $request['kode'],
            'data' => json_encode($payloads),
            'shop_product_id' => $response_create['product']['id'],
        ];
    
        \DB::table('products')->updateOrInsert(
            ['sku' => $request['kode']],
            $productData
        );
		
        return $response;
    }

   
}