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
    public function indexAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
        $context = $this->get('security.context');

        $centerDate = $slug;
        $edit = false;

        $datePeriod = $this->getDates($centerDate);
        $dates = $datePeriod[1];
        $startDate = $this->monthFromDate($datePeriod[0]);
        $header = $this->getDatesRow($dates);

        if($context->isGranted('ROLE_ADMIN')) {
            $edit = true;
            $events = $repository->findAll();
            $users = $this->getUsersFromEvents($events);
            foreach($users as $username) {
                $result[] = array('user'=> $username, 'row' => $this->getTableRow($repository, $username, $dates));
            }
        } else {
            $username = $context->getToken()->getUser()->getUsername();
            $result[] = array('user'=> $username, 'row' => $this->getTableRow($repository, $username, $dates));
        }



        return $this->render('WClockBundle:Report:report.html.twig', array(
            'table' => $result,
            'header' => $header,
            'date' => new \DateTime(),
            'edit' => $edit,
            'startDate' => $startDate
        ));
    }

    private function getUsersFromEvents($events) {
        // Event[] -> string[]
        // получить пользоваталей

        $users = array();

        foreach($events as $event) {
            $user = $event->getUserId();
            if(!in_array($user, $users))
                $users[] = $user;
        }

        return $users;
    }

    private function getTableRow($repository, $username, $dates) {
        // Repository, Event[] -> []

        $result = array();

        foreach($dates as $date) {
            $events = $repository->findBy(array('userId' => $username, 'date' => $date));
            $result[] = $this->getCell($events);
        }

        return $result;
    }

    private function getDates($center = false, $span = 31) {

        $interval = new \DateInterval('P'.$span.'D');

        $center = \DateTime::createFromFormat("dmY", $center);
        if($center == null) {
            $center = new \DateTime();
            $center->sub(new \DateInterval('P15D'));
        }




        $start = clone $center;
        $stop = clone $center;
        //$start->sub($interval);
        $stop->add($interval);

        return array($start,
            new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $stop
        ));
    }

    private function getDatesRow($dates) {
        $result = array();

        foreach($dates as $date) {
            $d = explode(".", $date->format(DATE_FORMAT));
            $result[] = array($d[0], $d[1]);
        }

        return $result;
    }

    private function getCell($events) {
        // Event[] -> string
        // вычислить данные для отображения в ячейке

        $count = count($events);
        $data = '';

        if($count == 0) {
            $count = '';
            $class = 'blank';
            $user = '';
            $day= '';
        } else {
            $workTime = calcDayWorkTime($events, true);
            $data = $workTime['hours'];
            if($data >= 8) { $class = 'normal'; } else {
                if($data == 0) { $class = 'red zero'; } else {
                    $class = 'red';
                }

            }
            $user = $events[0]->getUserId();
            $day= $events[0]->getDate()->format("d.m.Y");
        }

        $result = array(
            'data' => $data,
            'class' => $class,
            'user' => $user,
            'day' => $day
        );

        return $result;
    }

    private function monthFromDate($date) {
        $month = array(
            'январь',            'февраль',            'март',
            'апрель',            'май',            'июнь',
            'июль',            'август',            'сентябрь',
            'октябрь',            'ноябрь',            'декабрь'
        );
        return $month[intval($date->format("m")-1, 10)];
    }
}