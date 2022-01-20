<?php
namespace App\Service;

use Symfony\Component\Validator\Validation;

class DBValidationParser{
    //validate the request entries, use dependency injection for freedom of storing the constraintstrings
    //in a database of choice in fields of choice.
    public function validateDynamicRequest($request, DBValidationAdapter $resource){
        $itemsValidationArray = $resource->getItemsValidationObjectArray();
        $allViolationMessages = [];

        foreach ($itemsValidationArray as $typeName => $typeValidation){
            //validate each entry in the request that needs to be validated according to the DBValidationAdapter object.
            $violationMessages =
                $this->validateRequestItem($typeValidation, $request[$typeName]);
            //only add to the final array if the count of this violation messages > 0.
            //otherwise the result could be a not empty array of empty message arrays while the request is valid
            if (count($violationMessages)>0){
                $allViolationMessages[$typeName] = $violationMessages;
            }
        }
        return $allViolationMessages; //return violation message arrays for every request item.
    }

    public function validateRequestItem($constraints, $requestItem){
        $validator = Validation::createValidator();
        //convert the array to symfony validation constraints so they can be validated
        $convertedConstraints = $this->arrayToConstraintArray($constraints);
        //validate the request item against the converted symfony constraint
        $violations = $validator->validate($requestItem, $convertedConstraints);
        //convert the error messages from the violation objects and put them in an array
        $violationsMessages = $this->violationsToMessageStringArray($violations);
        return $violationsMessages; //return an array with error messages.
    }

    public function violationsToMessageStringArray($violations){
        $violationsMessages = array();
        foreach ($violations as $violation){
            array_push($violationsMessages, $violation->getMessage());
        }
        return $violationsMessages;
    }

    public function arrayToConstraintArray($constraints){
        $convertedConstraints = array();
        //loop over the associative array with their constraint name and their dedicated rules
        //and convert them tot a constraint
        foreach ($constraints as $constraintName => $rules){
            array_push($convertedConstraints, $this->objectToConstraint($constraintName, $rules));
        }
        return $convertedConstraints;
    }

    public function objectToConstraint($constraintName, $rules){
        $classname = 'Symfony\Component\Validator\Constraints\\'.$constraintName;
        //instantiate a new class of the given constraint name, with the dedicated rules array.
        $constraintClass = new $classname($rules);
        return $constraintClass; //return the converted constraint
    }
}