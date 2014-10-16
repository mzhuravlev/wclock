<?php

namespace LeaderIT\Bundle\WClockBundle\Controller;

use LeaderIT\Bundle\WClockBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;


class ReportController extends Controller
{
    const DATE_FORMAT = "d.m";

    public function statAction()
    {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');

        $context = $this->get('security.context');
        $edit = $context->isGranted('ROLE_ADMIN');

        $events = $repository->findAll();
        $users = $this->getUsersFromEvents($events);

        foreach($users as $user) {
            $events = $repository->findBy(array('userId' => $user), array('id' => 'asc'));
            $dates = $this->getDatesFromEvents($events);

            foreach($dates as $date) {

            }
        }

        return $this->render('WClockBundle:Report:stat.html.twig', array(
            'date' => new \DateTime(),
            'edit' => $edit
        ));
    }

    private function getDatesFromEvents($events) {
        $dates = array();

        foreach($events as $event) {
            $eventDate = $event->getDate();
            if(!in_array($eventDate, $dates))
                $dates[] = $eventDate;
        }

        return $dates;
    }

    public function indexAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()->getRepository('WClockBundle:Event');
        $markRepository = $this->getDoctrine()->getRepository('WClockBundle:DayMark');
        $context = $this->get('security.context');

        $centerDate = $slug;
        $edit = false;

        $datePeriod = $this->getDates($centerDate);
        $dates = $datePeriod[1];
        $startDate = $this->monthFromDate($datePeriod[0]);
        $startYear = $datePeriod[0]->format("Y");
        $header = $this->getDatesRow($dates);

        if ($context->isGranted('ROLE_ADMIN')) {
            $edit = true;
            $events = $repository->findAll();
            $users = $this->getUsersFromEvents($events);
            foreach ($users as $username) {
                $result[] = array('user' => $username, 'row' => $this->getTableRow($repository, $markRepository, $username, $dates));
            }
        } else {
            $username = $context->getToken()->getUser()->getUsername();
            $result[] = array('user' => $username, 'row' => $this->getTableRow($repository, $markRepository, $username, $dates));
        }

        return $this->render('WClockBundle:Report:report.html.twig', array(
            'table' => $result,
            'header' => $header,
            'date' => new \DateTime(),
            'edit' => $edit,
            'startDate' => $startDate,
            'startYear' => $startYear
        ));
    }

    private function getUsersFromEvents($events)
    {
        // Event[] -> string[]
        // получить пользоваталей

        $users = array();

        foreach ($events as $event) {
            $user = $event->getUserId();
            if (!in_array($user, $users))
                $users[] = $user;
        }

        return $users;
    }

    private function getTableRow($repository, $markRepository, $username, $dates)
    {
        // Repository, Event[] -> []

        $result = array();


        foreach ($dates as $date) {
            $events = $repository->findBy(array('userId' => $username, 'date' => $date), array('id' => 'ASC'));
            $result[] = $this->getCell($markRepository, $events, $date, $username);
        }

        return $result;
    }

    private function getDates($center = false, $span = 31)
    {

        $monthDays = array(
            31, 29, 31,
            30, 31, 30,
            31, 31, 30,
            31, 30, 31);

        $center = \DateTime::createFromFormat("dmY", $center);
        if ($center == null) {
            $center = new \DateTime();
            $center = $center->format("m.Y");
            $center = \DateTime::createFromFormat("d.m.Y", "01." . $center);
        }

        $span = $monthDays[intval($center->format("m")) - 1];
        $interval = new \DateInterval('P' . $span . 'D');


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

    private function getDatesRow($dates)
    {
        $result = array();

        foreach ($dates as $date) {
            $d = explode(".", $date->format(self::DATE_FORMAT));
            $result[] = array($d[0], $d[1]);
        }

        return $result;
    }

    private function formatWorkTime($time)
    {
        $hour = $time->h;
        $min = floor($time->i / 6);
        if ($min > 0)
            return $hour . "." . $min;

        return $hour;
    }

    private function getCell($markRepository, $events, $date, $username)
    {
        // Event[] -> string
        // вычислить данные для отображения в ячейке


        $count = count($events);
        $data = '';
        $mark = '';
        $comment = '';

        $dayMark = $markRepository->findBy(array('user' => $username, 'date' => $date));
        if($dayMark) {
            $dayMark = array_pop($dayMark);
            $mark = $dayMark->getType();
            $comment = $dayMark->getComment();
        }


        if ($count == 0) {
            $count = '';
            $class = 'blank';
            $user = '';
            $day = '';
        } else {
            $workTime = $this->get('wclock')->calcDayWorkTime($events);
            $data = $this->formatWorktime($workTime);
            if ($data >= 8) {
                $class = 'normal';
            } else {
                if ($data == 0) {
                    $class = 'red zero';
                } else {
                    $class = 'red';
                }

            }
            $user = $events[0]->getUserId();
            //$day = $date->format("d.m.Y");//$events[0]->getDate()->format("d.m.Y");
        }

        $day = $date->format("d.m.Y");
        $user = $username;

        $result = array(
            'data' => $data,
            'class' => $class,
            'user' => $user,
            'day' => $day,
            'mark' => $mark,
            'comment' => $comment
        );

        return $result;
    }

    private function monthFromDate($date)
    {
        $month = array(
            'январь', 'февраль', 'март',
            'апрель', 'май', 'июнь',
            'июль', 'август', 'сентябрь',
            'октябрь', 'ноябрь', 'декабрь'
        );
        return $month[intval($date->format("m") - 1, 10)];
    }
}