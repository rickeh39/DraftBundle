<?php

namespace App\Document;

use App\Service\DBValidationAdapter;

class DraftValidatorParser implements DBValidationAdapter
{
    private $validationArray;
    public function __construct(Draft $draft)
    {
        $this->validationArray = $this->parseMongoDBODMproxyToArray($draft);
    }

    private function parseMongoDBODMproxyToArray(Draft $draft){
        $validationArray = [];
        $contentTypes = $draft->getContentTypes()->getValues();
        foreach ($contentTypes as $contentType){
            $validationArray[$contentType->getTypeName()] = $contentType->getTypeValidation();
        }
        return $validationArray;
    }

    function getItemsValidationObjectArray()
    {
        return $this->validationArray;
    }
}