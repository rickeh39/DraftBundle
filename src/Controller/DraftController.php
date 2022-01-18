<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Draft;
use App\Document\DraftValidator;
use App\Document\Version;
use App\Form\Type\ArticleType;
use App\Service\DBValidationFacade;
use Doctrine\ODM\MongoDB\DocumentManager;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints as Constraints;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Validation;

/**
 * @Route("/draft")
 */
class DraftController extends AbstractController
{
    private $dm;
    private $val;

    public function __construct(DocumentManager $dm, DBValidationFacade $val)
    {
        $this->dm = $dm;
        $this->val = $val;
    }

    /**
     * @Route("/", name="draft_index")
     */
    public function index(): Response
    {
        $drafts = $this->dm->getRepository(Draft::class)->findAll();
        return $this->render('draft/index.html.twig', ['drafts' => $drafts]);
    }

    /**
     * @Route("/autosave/{id}", name="draft_autosave")
     * @Method("PUT")
     */
    public function autosave($id, Request $request)
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        return $this->saveDraft($request, $draft);
    }

    /**
     * @Route("/{id}/testvalidate", name="draft_testval")
     * @Method("GET")
     */
    public function testValidate($id)
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);

        return $this->saveDraft($draft->getContentValues(), $draft);
    }

    /**
     * @Route("/{id}", name="draft_show")
     */
    public function show($id): Response
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        return $this->render('draft/show.html.twig', ['draft' => $draft]);
    }

    /**
     * @Route("/{id}/create", name="draft_create")
     */
    public function createDraft(Request $request, $id): Response
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);

        $contentTypes = $draft->getContentTypes();

        $formOptions = ['contentTypes' => $contentTypes->getValues(),
            'contentValues' => []];

        $form = $this->createForm(ArticleType::class, $formOptions);
        $form->handleRequest($request);

        foreach ($form->getData()['contentTypes'] as $type){
            $draft->addContentTypes($type);
        }
        $this->dm->persist($draft);
        $this->dm->flush();

        return $this->render('draft/new.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'updatedAt' => $draft->getUpdatedAt()
        ));
    }

    /**
     * @Route("/{id}/edit", name="draft_edit")
     */
    public function edit(Request $request, $id): Response
    {
        $this->createDraftForArticle($id);

        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);

        $contentTypes = $draft->getContentTypes();

        $formOptions = ['contentTypes' => $contentTypes->getValues(),
            'contentValues' => $draft->getContentValues()];

        $form = $this->createForm(ArticleType::class, $formOptions);
        $form->handleRequest($request);
        $this->dm->persist($draft);
        $this->dm->flush();

        return $this->render('draft/edit.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'updatedAt' => $draft->getUpdatedAt()
        ));
    }

    /**
     * @Route("/{id}/remove", name="draft_remove")
     */
    public function remove($id): RedirectResponse
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $id]);

        if ($article != null)
        {
            $article->setDraft(null);
            $this->dm->persist($article);
        }

        $this->dm->remove($draft);
        $this->dm->flush();
        return $this->redirectToRoute('draft_index');
    }

    /**
     * @Route("/{id}/publish", name="draft_publish")
     */
    public function publish($id): RedirectResponse
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $article= $this->dm->getRepository(Article::class)->findOneBy(['id' => $draft->getId()]);
        $version = new Version();

        if ($article == null) {
            $article = new Article;
        }

        $this->oldToNewDocument($draft, $article);

        $article->setId($draft->getId());
        $article->setDraft(null);
        $article->addVersion($version);
        $version->setContent($draft->getContentValues());

        $this->dm->persist($article);
        $this->dm->persist($version);
        $this->dm->remove($draft);
        $this->dm->flush();

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    private function createDraftForArticle($id){
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $id]);

        if ($article != null) {
            if ($article->getDraft() == null) {
                $draft = new Draft;
                $this->oldToNewDocument($article, $draft);
                $draft->setId($article->getId());
                $draft->setArticle($article);
                $article->setDraft($draft);

                $this->dm->persist($draft);
                $this->dm->flush();
            }
        }
    }

    private function oldToNewDocument($oldDocument, $newDocument){
        $oldReflection = new ReflectionObject($oldDocument);
        $newReflection = new ReflectionObject($newDocument);

        foreach ($oldReflection->getProperties() as $property) {
            if ($newReflection->hasProperty($property->getName())) {
                $newProperty = $newReflection->getProperty($property->getName());
                $newProperty->setAccessible(true);
                $property->setAccessible(true);
                $newProperty->setValue($newDocument, $property->getValue($oldDocument));
            }
        }
    }

    private function saveDraft($request,Draft $draft){
        $data = $request;
        if ($request instanceof Request){
            $data = json_decode($request->getContent(), true);
        }
        $draftValidator = new DraftValidator($draft);
        $violations = $this->val->validateDraftRequest($data, $draftValidator);

        $status = 200;
        if (count($violations)===0){
            $draft->setContentValues($data);
            $draft->setUpdatedAt(date('Y-m-d H:i:s'));
            $this->dm->persist($draft);
            $this->dm->flush();
        } else {
            $status = 400;
        }

        return new JsonResponse(['updatedAt'=> $draft->getUpdatedAt(), 'errors' => $violations], $status);
    }
}
