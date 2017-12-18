<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 18.12.2017
 * Time: 13:29
 */

namespace bh\mailchimp;

use MailChimp\Ecommerce\Orders;

/**
 * Class OrdersBh
 * @package app\modules\mailchimp\components\api
 */
class OrdersBh extends Orders
{
    /**
     * createOrder
     * @param string $store_id
     * @param array $data
     * @return mixed
     */
    public function createOrder(string $store_id, array $data)
    {
        return self::execute("POST", "ecommerce/stores/{$store_id}/orders/", $data);
    }
}