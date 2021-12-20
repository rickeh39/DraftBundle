<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Draft;
use App\Form\Type\ArticleType;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/draft")
 */
class DraftController extends AbstractController
{
    /**
     * @Route("/", name="draft_index")
     * @Method("GET")
     */
    public function index(ManagerRegistry $managerRegistry)
    {
        $dm = $managerRegistry->getManager();
        $drafts = $dm->getRepository(Draft::class)->findAll();
        return $this->render('draft/index.html.twig', ['drafts' => $drafts]);
    }

    /**
     * @Route("/new", name="draft_new")
     * @Method("POST")
     */
    public function new(ManagerRegistry $managerRegistry, Request $request)
    {
        $draft = new Draft();
        $draft->setUser(1);
        $dm = $managerRegistry->getManager();

        $form = $this->createForm(ArticleType::class, $draft);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $dm->persist($task);
            $dm->flush();

            return $this->redirectToRoute('draft_show', ['id' => $draft->getId()]);
        }

        //do something
        return $this->renderForm('draft/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="draft_show")
     */
    public function show(DocumentManager $documentManager, $id)
    {
        $draft = $documentManager->getRepository(Draft::class)->findOneBy(['id' => $id]);
        return $this->render('draft/show.html.twig', ['draft' => $draft]);
    }

    /**
     * @Route("/{id}/edit", name="draft_edit")
     * @Method("PUT")
     */
    public function edit(DocumentManager $documentManager,Request $request, $id)
    {
        $article = $documentManager->getRepository(Article::class)->findOneBy(['id' => $id]);
        if($article->getDraft() == null) $this->articleToDraft($article);

        $form = $this->createForm(ArticleType::class, $article->getDraft());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $documentManager->persist($task);
            $documentManager->flush();
            $this->addFlash('success', "De draft is bijgewerkt");
            return $this->redirectToRoute('draft_show', ['id' => $task->getId()]);
        }

        return $this->renderForm('draft/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/publish", name="draft_publish")
     */
    public function publish(ManagerRegistry $managerRegistry,$id)
    {
        $dm = $managerRegistry->getManager();
        $draft = $dm->getRepository(Draft::class)->findOneBy(['id' => $id]);
        $article= $dm->getRepository(Article::class)->findOneBy(['id' => $draft->getId()]);
        if($article == null) $article = new Article;

        //TODO Er moet automatisch nog een nieuwe versie gegenereerd worden op het moment van publiceren
        $article->setTitle($draft->getTitle());
        $article->setContent($draft->getContent());
        $article->setDescription($draft->getDescription());
        $article->setUser($draft->getUser());
        $article->setDraft(null);

        $dm->persist($article);
        $dm->remove($draft);
        $dm->flush();

        return $this->redirectToRoute('article_index');
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
