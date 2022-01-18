<?php

namespace App\Controller;

use App\Document\ContentType;
use App\Form\Type\ContentTypeType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Document\Draft;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/contentType")
 */
class ContentTypeController extends AbstractController
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @Route("/", name="contentType")
     * @Method("GET")
     */
    public function index(Request $request): Response
    {
        $types = $this->dm->getRepository(ContentType::class)->findAll();
        $form = $this->createForm(ContentTypeType::class, $types);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $draft = new Draft();

            foreach ($form['selectedItems']->getData() as $contentType){
                $draft->addContentTypes($contentType);
            }
            $draft->setUser(1);

            $this->dm->persist($draft);
            $this->dm->flush();
            return $this->redirectToRoute('draft_create', ['id' => $draft->getId()]);
        }

        return $this->renderForm('contentType/index.html.twig', [
            'form' => $form,
        ]);
    }
}
