<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 18.12.2017
 * Time: 13:15
 */

namespace bh\mailchimp;


use MailChimp\Ecommerce\Carts;

/**
 * Class CartsBh
 * @package app\modules\mailchimp\components\api
 */
class CartsBh extends Carts
{
    /**
     * createCart
     * @param string $store_id
     * @param array $data
     * @return mixed
     */
    public function createCart(string $store_id, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$store_id}/carts", $data);
    }
}