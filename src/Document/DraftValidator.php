<?php

namespace App\Document;

use App\Service\DBValidationInterface;

class DraftValidator implements DBValidationInterface
{
    private $draft;
    public function __construct(Draft $draft)
    {
        $this->draft = $draft;
    }

    function getTypes()
    {
    }

    function getData()
    {
        return $this->draft->getContentTypes()->getValues();
    }

    function getValidationRulesByType($type)
    {
        return $this->draft->getContentTypes()->getValues()[$type];
    }

    function getTypeName($type)
    {
        return $this->draft->getContentTypes()->getValues()[$type]->getTypeName();
    }
}