<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use LeaderIT\Bundle\WClockBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;

require_once("event.php");

class ReportController extends Controller
{
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
        $context = $this->get('security.context');

        if($context->isGranted('ROLE_ADMIN')) {
            $events = $repository->findAll();
        } else {
            $username = $context->getToken()->getUser()->getUsername();
            $events = $repository->findBy(array('userId' => $username));
        }


        $result = getReadableEvents($events);

        /*$user = $request->request->get('user');
        $action = $request->request->get('action');

        $datetime = new \DateTime;

        $event = new Event();
        $event->setDate($datetime);
        $event->setTime($datetime);
        $event->setUserId($user);
        $event->setType(getActionType($action));



        $em->persist($event);
        $em->flush();


        $data = ['id' => $event->getId()];*/

        return $this->render('WClockBundle:Report:report.html.twig', array('events' => $result));
    }
}