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
        $actionType = getActionType($action);

        $datetime = new \DateTime;

        $event = new Event();
        $event->setDate($datetime);
        $event->setTime($datetime);
        $event->setUserId($user);
        $event->setType($actionType);


        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();


        //$data = ['id' => $event->getId(), 'user' => $user];
        $data = ['state' => $actionType];

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }

    public function stateAction() {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
        $context = $this->get('security.context');

        $username = $context->getToken()->getUser()->getUsername();
        $events = $repository->findBy(array('userId' => $username));

        $lastEvent = array_pop($events);
        $lastEventType = $lastEvent->getType();

        $data = ['state' => $lastEventType];

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }
}