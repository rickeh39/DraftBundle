<?php

namespace App\Controller;

use App\Document\ContentType;
use App\Form\Type\ContentTypeType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Document\Article;
use App\Document\Content;
use App\Document\Draft;
use App\Document\Version;
use App\Form\Type\ArticleType;
use ReflectionObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function index(): Response
    {
        $types = $this->dm->getRepository(ContentType::class)->findAll();
        $form = $this->createForm(ContentTypeType::class, $types);

        return $this->renderForm('contentType/index.html.twig', [
            'form' => $form,
        ]);
    }
}
