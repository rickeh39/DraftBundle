<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Version;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $articles = $dm->getRepository(Article::class)->findAll();
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/{id}", name="article_show")
     */
    public function show(DocumentManager $documentManager, $id)
    {
        $article = $documentManager->getRepository(Article::class)->findOneBy(['id' => $id]);
        return $this->render('article/show.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/{articleId}/{versionId}", name="article_version")
     */
    public function version(DocumentManager $documentManager, $versionId, $articleId)
    {
        $version = $documentManager->getRepository(Version::class)->findOneBy(['id' => $versionId]);
        $article = $documentManager->getRepository(Article::class)->findOneBy(['id' => $articleId]);

        return $this->render('article/version.html.twig', ['version' => $version, 'article' => $article]);
    }
}
