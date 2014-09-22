<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use LeaderIT\Bundle\WClockBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;

require_once("event.php");

class AjaxController extends Controller
{
    public function indexAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser()->getUsername();
        $action = $request->request->get('action');

        $datetime = new \DateTime;

        $event = new Event();
        $event->setDate($datetime);
        $event->setTime($datetime);
        $event->setUserId($user);
        $event->setType(getActionType($action));


        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();


        $data = ['id' => $event->getId(), 'user' => $user];

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }
}