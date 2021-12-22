<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Draft extends Content
{
    /** @MongoDB\ReferenceOne(targetDocument=Article::class) */
    protected $article;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    public function __construct(){
        parent::__construct();
        $this->updatedAt = date('d-m-y H:m:s');
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param mixed $article
     */
    public function setArticle($article): void
    {
        $this->article = $article;
    }

    /**
     * @return false|string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param false|string $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}