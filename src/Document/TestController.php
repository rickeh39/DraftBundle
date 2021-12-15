<?php

namespace App\Document;


use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class TestController extends Controller\AbstractController
{
    /**
     * @Route("/mongoTest")
     * @Method("GET")
     */
    public function someAction(ManagerRegistry $managerRegistry)
    {
        $user = new Gebruiker();
        $user->setFirstname("John");

        $dm = $managerRegistry->getManager();

        $dm->persist($user);
        $dm->flush();
        //do something
        return new Response('Saved new user with id '.$user->getId());
    }
}