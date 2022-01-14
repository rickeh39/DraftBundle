<?php
namespace App\Service;

use Symfony\Component\Validator\Validation;

class DBValidationFacade{
    public function validateDraftRequest($data, DBValidationInterface $draft){
        $types = $draft->getData();
        $allViolations = [];

        $count = 0;
        foreach ($types as $key => $type){
            $constraintViolations =
                $this->validateDataItem($draft->getValidationRulesByType($key), $data[$draft->getTypeName($key)]);
            $count+=count($constraintViolations);
            $allViolations[$draft->getTypeName($key)] = $constraintViolations;
        }
        return $count === 0 ? [] : $allViolations;
    }

    public function validateDataItem($constraints, $data){
        $validator = Validation::createValidator();

        $convertedConstraints = array();
        foreach ($constraints as $constraintName => $rules){
            array_push($convertedConstraints, $this->objectToConstraint($constraintName, $rules));
        }
        $violations = $validator->validate($data, $convertedConstraints);

        $violationsMessages = array();
        foreach ($violations as $violation){
            array_push($violationsMessages, $violation->getMessage());
        }
        return $violationsMessages;
    }

    public function objectToConstraint($constraintName, $rules){
        $classname = 'Symfony\Component\Validator\Constraints\\'.$constraintName;
        $constraintClass = new $classname($rules);
        return $constraintClass;
    }
}