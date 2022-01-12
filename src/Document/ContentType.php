<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
abstract class ContentType
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
    protected $typeFormbuild;

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
    public function getTypeFormbuild()
    {
        return $this->typeFormbuild;
    }

    /**
     * @param mixed $typeFormbuild
     */
    public function setTypeFormbuild($typeFormbuild): void
    {
        $this->typeFormbuild = $typeFormbuild;
    }
}