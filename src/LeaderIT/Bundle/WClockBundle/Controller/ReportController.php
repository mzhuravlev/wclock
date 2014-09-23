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

        $dates = $this->getDates($centerDate);
        $header = $this->getDatesRow($dates);

        if($context->isGranted('ROLE_ADMIN')) {
            $events = $repository->findAll();
            $users = $this->getUsersFromEvents($events);
            foreach($users as $username) {
                $result[] = ['user'=> $username, 'row' => $this->getTableRow($repository, $username, $dates)];
            }
        } else {
            $username = $context->getToken()->getUser()->getUsername();
            $result[] = ['user'=> $username, 'row' => $this->getTableRow($repository, $username, $dates)];
        }


        return $this->render('WClockBundle:Report:report.html.twig', ['table' => $result, 'header' => $header]);
    }

    private function getUsersFromEvents($events) {
        // Event[] -> string[]
        // получить пользоваталей

        $users = [];

        foreach($events as $event) {
            $user = $event->getUserId();
            if(!in_array($user, $users))
                $users[] = $user;
        }

        return $users;
    }

    private function getTableRow($repository, $username, $dates) {
        // Repository, Event[] -> []

        $result = [];

        foreach($dates as $date) {
            $events = $repository->findBy(['userId' => $username, 'date' => $date]);
            $result[] = $this->getCell($events);
        }

        return $result;
    }

    private function getDates($center = false, $span = 15) {

        $center = \DateTime::createFromFormat("dmY", $center);
        if($center == null) $center = new \DateTime();


        $interval = new \DateInterval('P'.$span.'D');

        $start = clone $center;
        $stop = clone $center;
        $start->sub($interval);
        $stop->add($interval);

        return new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $stop
        );
    }

    private function getDatesRow($dates) {
        $result = [];

        foreach($dates as $date) {
            $result[] = $date->format(DATE_FORMAT);
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
            if($workTime['hours'] >= 8) { $class = 'normal'; } else { $class = 'red'; }
            $user = $events[0]->getUserId();
            $day= $events[0]->getDate()->format("d.m.Y");
        }

        $result = [
            'data' => $data,
            'class' => $class,
            'user' => $user,
            'day' => $day
        ];

        return $result;
    }
}