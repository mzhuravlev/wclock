<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use LeaderIT\Bundle\WClockBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        $data = array('state' => $actionType);

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }

    public function stateAction() {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
        $context = $this->get('security.context');

        $username = $context->getToken()->getUser()->getUsername();
        $events = $repository->findBy(array('userId' => $username, 'date' => new \DateTime()), array('id' => 'ASC'));

        if(count($events) > 0) {
            $workTime = calcDayWorkTime($events, false, true);
            $lastEventType = $events[count($events) - 1]->getType();
        } else {
            $workTime = 0;
            $lastEventType = ACTION_NONE;
        }

        if($lastEventType == ACTION_BREAK) {
            $breaktime = calcTimeToNow(array_pop($events)->getTime());
        } else {
            $breaktime = 0;
        }

        $data = array('state' => $lastEventType, 'worktime' => $workTime, 'breaktime' => $breaktime);

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }

    public function infoAction(Request $request) {
        $user = $request->request->get('user');
        $day  = $request->request->get('day');

        if(!($user == '' or $day == '')) {
            $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
            $events = $repository->findBy(array('userId' => $user, 'date' => \DateTime::createFromFormat("d.m.Y", $day)), array('id' => 'ASC'));
            $result = getReadableEvents($events);
            $date = $events[0]->getDate()->format("d.m.Y");
            $time = calcDayWorkTime($events);
        } else {
            $date = null;
            $time = null;
            $result = array();
        }

        return $this->render('WClockBundle:Ajax:events.html.twig', array('result' => $result, 'date' => $date, 'time' => $time));
        //return $this->render('WClockBundle:Ajax:info.html.twig', ['user' => $user, 'day' => $day]);
    }

    public function editAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('WClockBundle:Event');
        $context = $this->get('security.context');

        $result = array('code' => 'fail');

        if($context->isGranted('ROLE_ADMIN')) {
            $id = $request->get('id');
            $time = $request->get('time');
            $type = $request->get('type');
            $delete = $request->get('delete') == 'true';
            $copy = $request->get('copy') == 'true';


            $time = \DateTime::createFromFormat("H:i", $time);
            // !!! сделать валидацию


            $event = $repository->find($id);
            if (!$event) {
                throw $this->createNotFoundException(
                    'Ќе найден Event, id = '.$id
                );
            } else {

                if($copy) {
                    // копируем
                    $newEvent = new Event();
                    $newEvent->setType($type);
                    $newEvent->setDate($event->getDate());
                    $newEvent->setTime($time);
                    $newEvent->setUserId($event->getUserId());

                    $em->persist($newEvent);
                } else {

                    if ($delete) {
                        $em->remove($event);
                    } else {
                        $event->setTime($time);
                        if ($type > 0)
                            $event->setType($type);
                    }
                }

                $em->flush();
                $result = array('code' => 'success', 'delete' => $delete, 'id' => $id);
            }
        }


        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $result));
    }
}