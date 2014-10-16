<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use LeaderIT\Bundle\WClockBundle\Entity\DayMark;
use LeaderIT\Bundle\WClockBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use LeaderIT\Bundle\WClockBundle\WClock;

class AjaxController extends Controller
{
    public function markAction(Request $request)
    {
        if(!$this->get('security.context')->isGranted('ROLE_ADMIN'))
            $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => array('error' => 'access denied')));

        $date = \DateTime::createFromFormat("d.m.Y", $request->request->get("date"));
        $user = $request->request->get("user");
        $type = $request->request->get("mark");
        $comment = $request->request->get("comment");

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('WClockBundle:DayMark');

        $dayMark = $repository->findBy(array(
            'date' => $date,
            'user' => $user
        ));

        if($dayMark) {
            $dayMark = array_pop($dayMark);
        } else {
            $dayMark = new DayMark();
            $dayMark->setDate($date);
            $dayMark->setUser($user);
        }

        $dayMark->setType($type);
        $dayMark->setComment($comment);
        $em->persist($dayMark);
        $em->flush();

        $data = array(
            'result' => 'success',
            'message' => 'mark updated'
//            'date' => $date->format("d.m.Y"),
//            'user' => $user,
//            'mark' => $type,
//            'comment' => $comment
        );
        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }

    public function indexAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser()->getUsername();
        $action = $request->request->get('action');
        $actionType = $this->getActionType($action);

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
            $workTime = $this->get('w_clock')->calcDayWorkTime($events, true);
            $lastEventType = $events[count($events) - 1]->getType();
        } else {
            $workTime = new \DateInterval("P0D");
            $lastEventType = Event::ACTION_NONE;
        }

        if($lastEventType == Event::ACTION_BREAK) {
            $breaktime = $this->get('w_clock')->calcTimeToNow(array_pop($events)->getTime());
        } else {
            $breaktime = new \DateInterval("P0D");
        }

        $data = array('state' => $lastEventType, 'worktime' => $workTime, 'breaktime' => $breaktime);

        return $this->render('WClockBundle:Ajax:ajax.json.twig', array('data' => $data));
    }

    public function infoAction(Request $request) {
        $user = $request->request->get('user');
        $day  = $request->request->get('day');

        if(!($user == '' or $day == '')) {
            $date = \DateTime::createFromFormat("d.m.Y", $day);//$events[0]->getDate()->format("d.m.Y");
            $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
            $events = $repository->findBy(array('userId' => $user, 'date' => $date), array('time' => 'ASC'));
            $time = $this->get('w_clock')->calcDayWorkTime($events);
            $result = $this->getReadableEvents($events);


        } else {
            $date = null;
            $time = null;
            $result = array();
        }

        return $this->render('WClockBundle:Ajax:events.html.twig', array('user' => $user, 'result' => $result, 'date' => $date->format("d.m.Y"), 'time' => $time));
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
                    'Не найден Event, id = '.$id
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

    function getReadableEvents($events) {
        // Event[] -> [][]
        // получить читаемые данные из списка Events

        $result = array();

        foreach($events as $rec) {
            $result[] = array(
                'id' => $rec->getId(),
                'userId' => $rec->getUserId(),
                'type' => Event::getActionText($rec->getType()),
                'date' => $rec->getDate(),
                'time' => $rec->getTime()
            );
        }

        return $result;
    }

    function getActionType($action) {
        // string -> int
        //

        switch($action) {
            case "action_work":
                return Event::ACTION_WORK;
            case "action_break":
                return Event::ACTION_BREAK;
            case "action_leave":
                return Event::ACTION_LEAVE;
            default:
                return Event::ACTION_NONE;
        }
    }
}