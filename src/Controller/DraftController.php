<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Draft;
use App\Document\Version;
use App\Form\Type\ArticleType;
use Doctrine\ODM\MongoDB\DocumentManager;
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
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @Route("/", name="draft_index")
     */
    public function index(): Response
    {
        $drafts = $this->documentManager->getRepository(Draft::class)->findAll();
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
            $this->documentManager->persist($form->getData());
            $this->documentManager->flush();
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
        $draft = $this->documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);

        $data = json_decode($request->getContent(), true);

        $draft->setTitle($data['title']);
        $draft->setDescription($data['description']);
        $draft->setContent($data['content']);
        $draft->setUpdatedAt( date('d-m-Y H:i:s'));

        $this->documentManager->persist($draft);
        $this->documentManager->flush();
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

        $data = json_decode($request->getContent(), true);

        $draft->setTitle($data['title']);
        $draft->setDescription($data['description']);
        $draft->setContent($data['content']);
        $draft->setUpdatedAt( date('d-m-Y H:i:s'));

        $this->documentManager->persist($draft);
        $this->documentManager->flush();
        return new JsonResponse(['newDraftId'=>$draft->getId(), 'updatedAt' => $draft->getUpdatedAt()]);
    }

    /**
     * @Route("/{id}", name="draft_show")
     */
    public function show($id): Response
    {
        $draft = $this->documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);
        return $this->render('draft/show.html.twig', ['draft' => $draft]);
    }

    /**
     * @Route("/{id}/edit", name="draft_edit")
     */
    public function edit(Request $request, $id)
    {
        $article = $this->documentManager->getRepository(Article::class)->findOneBy(['id' => $id]);

        $updatedAt = null;

        if ($article != null) {
            if ($article->getDraft() == null) $this->articleToDraft($article);
            $form = $this->createForm(ArticleType::class, $article->getDraft());
        } else {
            $draft = $this->documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);
            $updatedAt = $draft->getUpdatedAt();
            $form = $this->createForm(ArticleType::class, $draft);
        }

        $form->handleRequest($request);
        $this->documentManager->persist($form->getData());
        $this->documentManager->flush();

        return $this->render('draft/new.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'updatedAt' => $updatedAt
        ));
    }

    /**
     * @Route("/{id}/remove", name="draft_remove")
     */
    public function remove($id) {
        $draft = $this->documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $article = $this->documentManager->getRepository(Article::class)->findOneBy(['id' => $id]);

        if ($article != null)
        {
            $article->setDraft(null);
            $this->documentManager->persist($article);
        }

        $this->documentManager->remove($draft);
        $this->documentManager->flush();
        return $this->redirectToRoute('draft_index');
    }

    /**
     * @Route("/{id}/publish", name="draft_publish")
     */
    public function publish($id): RedirectResponse
    {
        $draft = $this->documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $article= $this->documentManager->getRepository(Article::class)->findOneBy(['id' => $draft->getId()]);
        $version = new Version;

        if ($article == null) {
            $article = new Article;
        }

        $version->setContent($draft->getContent());
        $article->addVersion($version);
        $article->setTitle($draft->getTitle());
        $article->setContent($draft->getContent());
        $article->setDescription($draft->getDescription());
        $article->setUser($draft->getUser());
        $article->setDraft(null);

        $this->documentManager->persist($version);
        $this->documentManager->persist($article);
        $this->documentManager->remove($draft);
        $this->documentManager->flush();

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    /**
     * @param Article $article
     */
    private function articleToDraft(Article $article){
        $draft = new Draft;

        $draft->setUser($article->getUser());
        $draft->setDescription($article->getDescription());
        $draft->setContent($article->getContent());
        $draft->setTitle($article->getTitle());
        $draft->setId($article->getId());

        $draft->setArticle($article);
        $article->setDraft($draft);
    }
}
