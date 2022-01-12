<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * @MongoDB\Document
 */
class Article extends Content
{
    /** @MongoDB\ReferenceOne(targetDocument=Draft::class, orphanRemoval=true) */
    protected $draft;

    /** @MongoDB\ReferenceMany(targetDocument=Version::class)*/
    protected $versions;

    public function __construct(){
        parent::__construct();
        $this->versions = new ArrayCollection();
    }

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

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    public function addVersion(Version $version){
        if ($this->versions->contains($version)){
            return;
        }
        $this->versions->add($version);
    }
}