<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 15.12.2017
 * Time: 17:48
 */

namespace bh\mailchimp;

use app\models\BasketModel;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class MailchimpEventPusher
 * @package MailchimpEventPusher
 */
class MailchimpEventPusher
{
    /** @var string $parent_class */
    private $parent_class = 'bh\mailchimp\MailchimpEventInterface';

    public function __construct()
    {
        $this->init();
    }

    /**
     * init
     */
    public function init()
    {
        $this->getEvents();
    }

    /**
     * getEvents
     */
    private function getEvents()
    {
        Event::on($this->parent_class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
            $sender = $event->sender;
            if ($sender instanceof $this->parent_class) {
                $mailchimpEvent = $sender->getMailchimpEvent();

                switch ($mailchimpEvent->getEntityType()) {
                    case 'product':
                        (new MailchimpManager())->updateProduct($mailchimpEvent);
                        break;
                    case 'customer':
                        (new MailchimpManager())->saveCustomer($mailchimpEvent);
                        break;
                    case 'basket':
                        (new MailchimpManager())->createCart($mailchimpEvent);
                        break;
                    case 'order':
                        //(new MailchimpManager())->createOrder($mailchimpEvent);
                        break;
                }
            }
        });

        Event::on($this->parent_class, ActiveRecord::EVENT_AFTER_UPDATE, function ($event) {
            /** @var MailchimpEventInterface $sender */
            $sender = $event->sender;

            /** @var MailchimpEvent $mailchimpEvent*/
            if ($mailchimpEvent = $sender->getMailchimpEvent()) {
                switch ($mailchimpEvent->getEntityType()) {
                    case 'product':
                        (new MailchimpManager())->updateProduct($mailchimpEvent);
                        break;
                    case 'customer':
                        (new MailchimpManager())->saveCustomer($mailchimpEvent);
                        break;
                    case 'basket':
                        (new MailchimpManager())->updateCart($mailchimpEvent);
                        break;
                    case 'order':
                        (new MailchimpManager())->updateOrder($mailchimpEvent);
                        break;
                }
            }

        });

        //TODO добавить удаление на какой-о флаг внутки передавайемой data, т.к. нет физического удаления
        Event::on($this->parent_class, ActiveRecord::EVENT_AFTER_DELETE, function ($event) {
            $sender = $event->sender;
            if ($sender instanceof $this->parent_class) {
                $mailchimpEvent = $sender->getMailchimpEvent();
                switch ($mailchimpEvent->getEntityType()) {
                    case 'product':
                        (new MailchimpManager())->removeProduct($mailchimpEvent);
                        break;
                }
            }
        });
    }
}
