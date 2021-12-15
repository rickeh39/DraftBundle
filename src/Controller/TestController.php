<?php

namespace App\Controller;


use App\Document\Gebruiker;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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