<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Version;
use App\Form\Type\ArticleType;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(ManagerRegistry $managerRegistry): Response
    {
        $documentManager = $managerRegistry->getManager();
        $articles = $documentManager->getRepository(Article::class)->findAll();
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/{id}", name="article_show")
     */
    public function show(DocumentManager $documentManager, $id): Response
    {
        $article = $documentManager->getRepository(Article::class)->findOneBy(['id' => $id]);
        return $this->render('article/show.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/{id}/remove", name="article_remove")
     */
    public function remove(ManagerRegistry $managerRegistry, $id){
        $dm = $managerRegistry->getManager();
        $article = $dm->getRepository(Article::class)->findOneBy(['id' => $id]);

        $dm->remove($article);
        $dm->flush();
        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/{articleId}/{versionId}", name="article_version")
     */
    public function version(DocumentManager $documentManager, $versionId, $articleId): Response
    {
        $version = $documentManager->getRepository(Version::class)->findOneBy(['id' => $versionId]);
        $article = $documentManager->getRepository(Article::class)->findOneBy(['id' => $articleId]);
        return $this->render('article/version.html.twig', ['version' => $version, 'article' => $article]);
    }
}
