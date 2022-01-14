<?php
namespace App\Service;

interface DBValidationInterface{
    function getTypes();
    function getData();
    function getValidationRulesByType($type);
    function getTypeName($type);
}