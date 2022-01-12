<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @MongoDB\Document
 */
abstract class Content
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Type(type="TextType")
     */
    protected $title;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Type(type="TextType")
     */
    protected $description;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Type(type="TextAreaType")
     */
    protected $content;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $createDate;

    /**
     * @MongoDB\Field(type="integer")
     */
    protected $user;

    /**
     * @MongoDB\ReferenceMany(targetDocument=ContentType::class)
     */
    protected $contentTypes;

    public function __construct(){
        $this->createDate = date('Y-m-d H:i:s');
        $this->contentTypes = new ArrayCollection();
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

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
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

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate): void
    {
        $this->createDate = $createDate;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection | PersistentCollection
     */
    public function getContentTypes()
    {
        return $this->contentTypes;
    }

    public function addContentTypes(ContentType $contentType)
    {
        if ($this->contentTypes->contains($contentType)){
            return;
        }
        $this->contentTypes->add($contentType);
    }
}