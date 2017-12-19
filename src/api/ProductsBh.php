<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 14.12.2017
 * Time: 15:52
 */
namespace bh\mailchimp\api;

use MailChimp\Ecommerce\Products;

/**
 * Class ProductsBh
 * @package app\components\mailchimp\api
 */
class ProductsBh extends Products
{
    /**
     * updateProduct
     * @param $store_id
     * @param $product_id
     * @param array $data
     * @return mixed
     */
    public function updateProduct($store_id, $product_id, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}/products/{$product_id}", $data);
    }

    /**
     * addProduct
     * @param string $store_id
     * @param array $data
     * @return mixed|object
     */
    public function createProduct($store_id, array $data = [])
    {
        return self::execute("POST", "ecommerce/stores/{$store_id}/products", $data);

    }
}
