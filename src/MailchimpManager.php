<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 14.12.2017
 * Time: 13:45
 */
namespace bh\mailchimp-event-pusher;

use MailChimp\MailChimp;
use yii\caching\FileCache;

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
        $this->mcAgent = new MailChimp();
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
        return new CartsBh();
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
            if ($product = $this->getProductExternal($item)) {
                $this->updateProduct($item, (array)$product);
            } else {
                $this->addProduct($item);
            }
        }
    }*/

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
     * addProduct
     * @param MailchimpEvent $event
     * @return bool|mixed
     */
    public function addProduct(MailchimpEvent $event) {
        return $this->makeCall(
            $this->getProducts(),
            'createProduct',
            [$this->store_id, (string)$event->getEntityId(), $event->getData()]
        );
    }

    /**
     * getProductExternal
     * @param string $id
     * @return bool|mixed
     */
    private function getProductExternal(string $id)
    {
        $cacheKey = 'product_'.$this->store_id.'_'.$id;
        $obCache = (new FileCache());
        if (!$answer = $obCache->get($cacheKey)) {
            if ($answer = $this->makeCall($this->getProducts(), 'getProduct', [$this->store_id, $id])) {
                $obCache->add($cacheKey, $answer, 3600);
            }
        };

        return $answer;

    }

    /**
     * updateProduct
     * @param MailchimpEvent $event
     * @return bool
     */
    public function updateProduct(MailchimpEvent $event)
    {
        $product = (array)$this->getProductExternal($event->getEntityId());
        $itemData = $event->getData();

        if ($product['type'] != $itemData['type'] || count($product['variants']) <> count($itemData['variants'])) {
            $newVariants = $itemData['variants'];
            $oldVariants = $product['variants'];
            //удаляем старые варинаты и записываем новые (и изменяем последний из старых на новый, т.к. полностью все удалить нельзя)
            if ($oldVariants) {
                foreach ($oldVariants as $var) {
                    if (!$this->makeCall($this->getProducts(), 'deleteVariant', [$this->store_id, $event->getEntityId(), $var->id])) {
                        $found = false;
                        foreach ($newVariants as $variant) {
                            if ($variant['id'] == $var->id) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $toUpdateVariant = $var;
                        }
                    }
                }

            };

            $this->makeCall($this->getProducts(), 'updateProduct', [$this->store_id, $event->getEntityId(), $event->getData()]);

            if ($toUpdateVariant) {
                $this->makeCall($this->getProducts(), 'deleteVariant', [$this->store_id, $event->getEntityId(), $toUpdateVariant->id]);
            }
        } else {
            $this->makeCall($this->getProducts(), 'updateProduct', [$this->store_id, (string)$event->getEntityId(),$event->getData()]);
        };

        $this->deleteProductCache($event->getEntityId());

        return true;
    }

    /**
     * getProductVariantsExternal
     * @param string $id
     * @return array|bool|mixed
     */
    private function getProductVariantsExternal(string $id)
    {
        $cacheKey = 'product_variants_'.$this->store_id.'_'.$id;
        $obCache = (new FileCache());

        if (!$answer = $obCache->get($cacheKey)) {
            if ($data = $this->makeCall($this->getProducts(), 'getVariants', [$this->store_id, $id])) {
                if ($data->variants) {
                    foreach ($data->variants as $var) {
                        $answer[] = $var;
                    }
                };
                $obCache->add($cacheKey, $answer, 3600);
            } else {
                $answer = false;
            }
        };

        return $answer;
    }

    /**
     * deleteProductCache
     * @param string $id
     */
    private function deleteProductCache(string $id)
    {
        $cacheKey = 'product_variants_'.$this->store_id.'_'.$id;
        $obCache = (new FileCache());
        $obCache->delete($cacheKey);
        $cacheKey = 'product_'.$this->store_id.'_'.$id;
        $obCache->delete($cacheKey);
    }

    /**
     * deleteProduct
     * @param MailchimpEvent $event
     * @return bool
     */
    public function deleteProduct(MailchimpEvent $event)
    {
        $this->makeCall($this->getProducts(), 'deleteProduct', [$this->store_id, (string)$event->getEntityId()]);
        return true;
    }

    /**
     * createCart
     * @param MailchimpEvent $event
     * @return bool|mixed
     */
    public function createCart(MailchimpEvent $event)
    {
        return $this->makeCall($this->getCarts(), 'createCart', [$this->store_id, $event->getData()]);
    }

    /**
     * updateCart
     * @param MailchimpEvent $event
     * @return bool|mixed
     */
    public function updateCart(MailchimpEvent $event)
    {
        return $this->makeCall($this->getCarts(), 'updateCart', [$this->store_id, (string)$event->getEntityId(), $event->getData()]);
    }

    /**
     * deleteCart
     * @param string $id
     */
    public function deleteCart(string $id)
    {
        $this->makeCall($this->getCarts(), 'deleteCart', [$this->store_id, (string)$id]);
    }

    /**
     * getCart
     * @param string $id
     * @return bool|mixed
     */
    public function getCart(string $id)  {
        $cacheKey = 'carts_'.$this->store_id.'_'.$id;
        $obCache = (new FileCache());
        if (!$answer = $obCache->get($cacheKey)) {
            if ($answer = $this->makeCall($this->getCarts(), 'getCart', [$this->store_id, $id])) {
                $obCache->add($cacheKey, $answer, 3600);
            }
        };

        return $answer;
    }


    /**
     * createOrder
     * @param MailchimpEvent $event
     */
    public function createOrder(MailchimpEvent $event)
    {
        if (isset($event->getData()['basket_id'])) {
            $basket_id = $event->getData()['basket_id'];
            $data = $event->getData();
            unset($data['basket_id']);
            $event->setData($data);
        };
        if ($this->makeCall($this->getOrders(), 'createOrder', [$this->store_id, (string)$event->getEntityId(), $event->getData()]) && $basket_id) {
            return $this->deleteCart($basket_id);
        };
    }

    /**
     * updateOrder
     * @param MailchimpEvent $event
     * @return bool|mixed
     */
    public function updateOrder(MailchimpEvent $event)
    {
        if (isset($event->getData()['basket_id'])) {
            $data = $event->getData();
            unset($data['basket_id']);
            $event->setData($data);
        };
        return $this->makeCall($this->getOrders(), 'updateOrder', [
            $this->store_id, (string)$event->getEntityId(), $event->getData()
        ]);
    }

    /**
     * deleteOrder
     * @param int $id
     * @return bool|mixed
     */
    public function deleteOrder(int $id)
    {
        return $this->makeCall($this->getOrders(), 'deleteOrder', [
            $this->store_id, (string)$id
        ]);
    }
}
