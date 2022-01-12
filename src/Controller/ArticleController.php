<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\Version;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @Route("/", name="article_index")
     * @Method("GET")
     */
    public function index(): Response
    {
        $articles = $this->dm->getRepository(Article::class)->findAll();
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/{id}", name="article_show")
     */
    public function show($id): Response
    {
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $id]);
        return $this->render('article/show.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/{id}/remove", name="article_remove")
     */
    public function remove($id){
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $id]);

        $this->dm->remove($article);
        $this->dm->flush();
        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/{articleId}/{versionId}", name="article_version")
     */
    public function version($versionId, $articleId): Response
    {
        $version = $this->dm->getRepository(Version::class)->findOneBy(['id' => $versionId]);
        $article = $this->dm->getRepository(Article::class)->findOneBy(['id' => $articleId]);
        return $this->render('article/version.html.twig', ['version' => $version, 'article' => $article]);
    }
}
