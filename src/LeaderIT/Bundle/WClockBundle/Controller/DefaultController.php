<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {

        return $this->render('WClockBundle:Default:index.html.twig', array('name' => 'vasya'));
    }
}
