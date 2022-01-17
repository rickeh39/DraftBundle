<?php
namespace App\Service;

use App\Document\ContentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;

class ContentTypeGenerator
{
    private $dm;
    private $logger;

    public function __construct(DocumentManager $dm, LoggerInterface $logger)
    {
        $this->dm = $dm;
        $this->logger = $logger;
    }

    public function generate()
    {
        $contentTypes = $this->dm->getRepository(ContentType::class)->findAll();

        $newCollection = array(
            $this->setContentType('Title', 'TextType', []),
            $this->setContentType('Description', 'TextType', []),
            $this->setContentType('Content', 'TextAreaType', []),
            $this->setContentType('Slug', 'TextType', [
                'Length' => [
                    'min' => 4,
                    'max' => 14
                ],
                'Regex' => [
                    'pattern' => '/^[-a-zA-Z0-9_]+$/i',
                    'message' => 'Uw slug bevat illegale karakters.'
                ]
            ]),
            $this->setContentType('Video', 'TextType', [])
        );

        foreach ($newCollection as $item) {
            if ($this->myArrayContainsWord($contentTypes, $item->getTypeName()) == false) {
                $this->dm->persist($item);
                $this->dm->flush();
                $this->logger->info('Items are added to the ContentTypes document.');
            }
        }
    }

    private function setContentType($name, $formBuild, $validation)
    {
        $item = new ContentType();

        $item->setTypeName($name);
        $item->setTypeFormBuild($formBuild);
        $item->setTypeValidation($validation);

        return $item;
    }

    private function myArrayContainsWord(array $myArray, $word)
    {
        foreach ($myArray as $object) {
            if (property_exists($object, 'typeName') && $object->getTypeName() === $word) {
                return true;
            }
        }
        return false;
    }
}
