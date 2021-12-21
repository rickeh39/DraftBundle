<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Version {

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $versionDate;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $content;

    public function __construct(){
     $this->versionDate = date('d-m-y H:m:s');
    }

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

    public function getVersionDate()
    {
        return $this->version_date;
    }

    public function setVersionDate($version_date): void
    {
        $this->version_date = $version_date;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }
}