<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ContentType
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $typeName;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $typeFormBuild;


    /**
     * @MongoDB\Field(type="hash")
     */
    protected $typeValidation;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * @param mixed $typeName
     */
    public function setTypeName($typeName): void
    {
        $this->typeName = $typeName;
    }

    /**
     * @return mixed
     */
    public function getTypeFormBuild()
    {
        return $this->typeFormBuild;
    }

    /**
     * @param mixed $typeFormBuild
     */
    public function setTypeFormBuild($typeFormBuild): void
    {
        $this->typeFormBuild = $typeFormBuild;
    }

    /**
     * @return mixed
     */
    public function getTypeValidation()
    {
        return $this->typeValidation;
    }

    /**
     * @param mixed $typeValidation
     */
    public function setTypeValidation($typeValidation): void
    {
        $this->typeValidation = $typeValidation;
    }
}