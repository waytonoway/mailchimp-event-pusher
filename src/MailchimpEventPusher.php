<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 15.12.2017
 * Time: 17:48
 */

namespace bh\mailchimp;


use MailChimp\MailChimp;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class MailchimpEventPusher
 * @package MailchimpEventPusher
 */
class MailchimpEventPusher
{
    public function init()
    {
        Event::on('MailchimpEventPusher\MailchimpEventInterface', ActiveRecord::EVENT_AFTER_UPDATE, function ($event) {
            $sender = $event->sender;
            if ($sender instanceof MailchimpEventInterface) {
                $mailchimpEvent = $sender->getMailchimpEvent();
                (new MailchimpManager())->saveCustomer($mailchimpEvent);
            }
        });

        Event::on('MailchimpEventPusher\MailchimpEventInterface', ActiveRecord::EVENT_AFTER_UPDATE, function ($event) {
            $sender = $event->sender;
            if ($sender instanceof MailchimpEventInterface) {
                $mailchimpEvent = $sender->getMailchimpEvent();
                (new MailchimpManager())->saveCustomer($mailchimpEvent);
            }
        });
    }
}