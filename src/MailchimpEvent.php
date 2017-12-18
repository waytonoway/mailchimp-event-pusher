<?php
namespace bh\mailchimp;

/**
 * Class MailchimpEntityEvent
 * @package MailchimpEventPusher
 */
class MailchimpEvent
{

    private $entity_id = '';
    private $entity_type = '';
    private $data = [];

    public function setEntityId(string $id)
    {
        $this->entity_id = $id;

        return $this;
    }

    public function getEntityId(): string
    {
        return $this->entity_id;
    }

    public function setEntityType(string $type)
    {
        $this->entity_type = $type;

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->entity_type;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
