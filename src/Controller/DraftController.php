<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Content;
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
    public function new(Request $request)
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
    }

    /**
     * @Route("/autosave/{id}", name="draft_autosave")
     * @Method("PUT")
     */
    public function autosave($id, Request $request)
    {
        $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $this->saveDraft($request,$draft);
        return new JsonResponse(['updatedAt'=> $draft->getUpdatedAt()]);
    }
    /**
     * @Route("/firstautosave", name="draft_autosave_first")
     * @Method("PUT")
     */
    public function autosaveFirst(Request $request)
    {
        $draft = new Draft();
        $draft->setUser(1);
        $this->saveDraft($request,$draft);
        return new JsonResponse(['newDraftId'=>$draft->getId(), 'updatedAt' => $draft->getUpdatedAt()]);
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
            } else {
                $draft = $this->dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
            }
        }

        $form = $this->createForm(ArticleType::class, $draft);
        $form->handleRequest($request);
        $this->dm->persist($form->getData());
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

    private function saveDraft(Request $request,Draft $draft){
        $data = json_decode($request->getContent(), true);
        $draft->setTitle($data['title']);
        $draft->setDescription($data['description']);
        $draft->setContent($data['content']);
        $draft->setUpdatedAt( date('d-m-Y H:i:s'));
        $this->dm->persist($draft);
        $this->dm->flush();
    }
}
