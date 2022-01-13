<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Draft;
use App\Document\Version;
use App\Form\Type\ArticleType;
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

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
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
     * @Route("/new", name="draft_new")
     */
   /* public function new(Request $request)
    {
        $draft = new Draft();
        $draft->setUser(1);

        $form = $this->createForm(ArticleType::class, $draft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($form->getData());
            $this->dm->flush();
            return $this->redirectToRoute('draft_show', ['id' => $draft->getId()]);
        }

        return $this->renderForm('draft/new.html.twig', [
            'form' => $form,
            'updatedAt' => $draft->getUpdatedAt()
        ]);
    }*/

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
    public function testvalidate($id)
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
        //$draft = null;

        /*if ($draft != null) {
            if ($article->getDraft() == null) {
                $draft = new Draft;
                $this->oldToNewEntity($article, $draft);
                $draft->setId($article->getId());
                $draft->setArticle($article);
                $article->setDraft($draft);
            } else {
                $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
            }
        }*/

        $contentTypes = $draft->getContentTypes();

        $formoptions = ['contentTypes' => $contentTypes->getValues(),
            'contentValues' => []];

        $form = $this->createForm(ArticleType::class, $formoptions);
        $form->handleRequest($request);

        foreach ($form->getData()['contentTypes'] as $type){
            $draft->addContentTypes($type);
        }
        $this->dm->persist($draft);
        $this->dm->flush();

        return $this->render('draft/edit.html.twig', array(
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
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $id]);
        $draft = null;

        if ($article != null) {
            if ($article->getDraft() == null) {
                $draft = new Draft;
                $this->oldToNewEntity($article, $draft);
                $draft->setId($article->getId());
                $draft->setArticle($article);
                $article->setDraft($draft);
            }
            $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        }
        else {
            $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        }

        $contentTypes = $draft->getContentTypes();

        $formoptions = ['contentTypes' => $contentTypes->getValues(),
            'contentValues' => $draft->getContentValues()];

        $form = $this->createForm(ArticleType::class, $formoptions);
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

        $this->oldToNewEntity($draft, $article);

        $article->setId($draft->getId());
        $article->setDraft(null);
        $article->addVersion($version);

        $version->setContent($draft->getContent());

        $this->dm->persist($article);
        $this->dm->persist($version);
        $this->dm->remove($draft);
        $this->dm->flush();

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    private function oldToNewEntity($oldEntity, $newEntity){
        $oldReflection = new ReflectionObject($oldEntity);
        $newReflection = new ReflectionObject($newEntity);

        foreach ($oldReflection->getProperties() as $property) {
            if ($newReflection->hasProperty($property->getName())) {
                $newProperty = $newReflection->getProperty($property->getName());
                $newProperty->setAccessible(true);
                $property->setAccessible(true);
                $newProperty->setValue($newEntity, $property->getValue($oldEntity));
            }
        }
    }

    private function saveDraft($request,Draft $draft){
        $data = $request;
        if ($request instanceof Request){
            $data = json_decode($request->getContent(), true);
        }
        $violations = $this->validateDraftRequest($data, $draft);

        $status = 200;
        if (count($violations)===0){
            $draft->setContentValues($data);
            $this->dm->persist($draft);
            $this->dm->flush();
        } else {
            $status = 400;
        }

        return new JsonResponse(['updatedAt'=> $draft->getUpdatedAt(), 'errors' => $violations], $status);
    }

    private function validateDraftRequest($data, $draft){
        $types = $draft->getContentTypes()->getValues();
        $allViolations = [];

        $count = 0;
        foreach ($types as $type){
            $constraintViolations =
                $this->validateDataItem($type->getTypeValidation(), $data[$type->getTypeName()]);
            $count+=count($constraintViolations);
            $allViolations[$type->getTypeName()] = $constraintViolations;
        }
        return $count === 0 ? [] : $allViolations;
    }

    private function validateDataItem($constraints, $data){
        $validator = Validation::createValidator();

        $convertedConstraints = array();
        foreach ($constraints as $constraintName => $rules){
            array_push($convertedConstraints, $this->objectToConstraint($constraintName, $rules));
        }
        $violations = $validator->validate($data, $convertedConstraints);

        $violationsMessages = array();
        foreach ($violations as $violation){
            array_push($violationsMessages, $violation->getMessage());
        }
        return $violationsMessages;
    }

    private function objectToConstraint($constraintName, $rules){
        $classname = 'Symfony\Component\Validator\Constraints\\'.$constraintName;
        $constraintClass = new $classname($rules);
        return $constraintClass;
    }
}
