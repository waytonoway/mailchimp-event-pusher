<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 14.12.2017
 * Time: 13:45
 */
namespace bh\mailchimp;

use MailChimp\MailChimp;

/**
 * Class MailchimpManager
 */
class MailchimpManager
{
    /** @var bool|\MailChimp\MailChimp  */
    private $mcAgent = false;
    private $store_id = 'Gs_checkout_dev';

    /**
     * MailchimpManager constructor.
     */
    public function __construct()
    {
        $this->mcAgent = new MailChimp;
    }

    /**
     * getProducts
     * @return ProductsBh
     */
    public function getProducts()
    {
        return new ProductsBh();
    }

    /**
     * getCarts
     * @return \MailChimp\Ecommerce\Carts
     */
    public function getCarts()
    {
        return $this->mcAgent->ecommerce()->carts();
    }

    /**
     * getCustomers
     * @return \MailChimp\Ecommerce\Customers
     */
    public function getCustomers()
    {
        return $this->mcAgent->ecommerce()->customers();
    }

    /**
     * getOrders
     * @return \MailChimp\Ecommerce\Orders
     */
    public function getOrders()
    {
        return $this->mcAgent->ecommerce()->orders();
    }


    /**
     * makeCall
     * @param $object
     * @param $method
     * @param $args
     * @return bool|mixed
     */
    private function makeCall($object, $method, $args)
    {
        $answer = call_user_func_array(array($object, $method), $args );
        print_r($answer);
        return (int)$answer->status > 0 ? false : $answer;
    }

    /**
     * exportProducts
     * @param array[] $products
     *
    public function exportProducts(array $products)
    {
        foreach ($products as $item) {
            if ($product = $this->getProduct($item)) {
                $this->updateProduct($item, (array)$product);
            } else {
                $this->addProduct($item);
            }
        }
    }

    /**
     * saveCustomer
     * @param MailchimpEvent $event
     * @return bool|mixed
     */
    public function saveCustomer(MailchimpEvent $event)
    {
        return $this->makeCall(
            $this->getCustomers(),
            'upsertCustomer',
            [
                $this->store_id,
                (string)$event->getEntityId(),
                $event->getData()
            ]
        );
    }


    /**
     * updateProduct
     * @param MailchimpEvent $event
     * @return bool
     */
    public function updateProduct(MailchimpEvent $event)
    {
        /*if (($product['type'] == 'free' && $item->price == 0) || ($product['type'] == 'licence' && $item->price > 0)) {
            $this->makeCall($this->getProducts(), 'updateProduct', [$this->store_id, $item->code, ['id' => $item->code, 'title' => $item->name, 'type' => $item->price == 0 ? 'free' : 'licence', 'variants' => $this->makeVariants($item)]]);

            $this->deleteProductCache($item);
        } elseif (($product['type'] == 'free' && $item->price > 0) || ($product['type'] == 'licence' && $item->price == 0)) {
            $newVariants = $this->makeVariants($item);
            //удаляем старые варинаты и записываем новые (и изменяем послежний из старых на новый, т.к. полностью все удалить нельзя)
            if ($oldVars = $this->getProductVariants($item)) {
                foreach ($oldVars as $var) {
                    if (!$this->makeCall($this->getProducts(), 'deleteVariant', [$this->store_id, $item->code, $var->id])) {
                        $lastVariant = $var;
                    }
                }
                $newVariants = $this->makeVariants($item);
                $toChange = array_pop($newVariants);
                $this->makeCall($this->getProducts(), 'upsertVariant', [$this->store_id, $item->code, $lastVariant->id, $toChange]);
            };

            $this->makeCall($this->getProducts(), 'updateProduct', [$this->store_id, $item->code, ['id' => $item->code, 'title' => $item->name, 'type' => $item->price == 0 ? 'free' : 'licence', 'variants' => $newVariants]]);
            $this->deleteProductCache($item);
        }
        /*foreach ($this->makeVariants($item) as $var) {
            $this->getProducts()->upsertVariant($this->store_id, $item->code, $var['id'], $var);
        }*/

        $this->makeCall($this->getProducts(), 'updateProduct', [$this->store_id, (string)$event->getEntityId(),$event->getData()]);
        return true;
    }

    /**
     * makeVariants
     * @param ProductModel $item
     * @return array
     */
    private function makeVariants(ProductModel $item)
    {
        $variants = [];
        if ($item->price == 0) {
            $variants[] = ['id' => $item->code, 'title' => $item->name, 'price' => $item->price];
        } else {
            foreach (Preferences::license_types as $type) {
                $variants[] = ['id' => $item->code.'_'.$type, 'title' => $item->name, 'price' => $item->price *
                    constant('\app\components\service\Preferences::'.$type)];
            }
        };

        return $variants;
    }

}