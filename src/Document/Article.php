<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
/**
 * @MongoDB\Document
 */
class Article extends Content
{
    /** @MongoDB\ReferenceOne(targetDocument=Draft::class) */
    protected $draft;

    /**
     * @return mixed
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * @param mixed $draft
     */
    public function setDraft($draft): void
    {
        $this->draft = $draft;
    }
}