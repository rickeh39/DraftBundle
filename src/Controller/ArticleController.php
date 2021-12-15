<?php

namespace App\Controller;

use App\Document\Article;
use App\Form\Type\ArticleType;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index")
     * @Method("GET")
     */
    public function index(ManagerRegistry $managerRegistry)
    {
        $dm = $managerRegistry->getManager();
        $articles = $dm->getRepository(Article::class)->findAll(); if (!$articles) { throw $this->createNotFoundException('No product found for id ' . 1); }
//        dd($article);
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/new", name="article_new")
     * @Method("POST")
     */
    public function new(ManagerRegistry $managerRegistry, Request $request)
    {
        $article = new Article();
        $article->setUser(1);
        $dm = $managerRegistry->getManager();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $dm->persist($task);
            $dm->flush();

            return $this->redirectToRoute('task_success');
        }

        //do something
        return $this->renderForm('article/new.html.twig', [
            'form' => $form,
        ]);
    }
}
