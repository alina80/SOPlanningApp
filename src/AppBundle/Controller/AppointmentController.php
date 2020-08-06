<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Appointment;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends Controller
{
    /**
     * @Route("/addAppointment")
     */
    public function addAppointmentAction()
    {
        $em = $this->getDoctrine()->getManager();
        $statusRepo = $em->getRepository('AppBundle:Status');
        $statuses = $statusRepo->findAll();
        return $this->render('AppBundle:Appointment:add_appointment.html.twig', [
            'statuses'=>$statuses
        ]);
    }

    /**
     * @Route("/saveAppointment", methods={"POST"})
     * @param Session $session
     * @param Request $req
     * @return Response|null
     */
    public function saveAppointmentAction(Session $session, Request $req)
    {
        print_r($_POST);

        $data = $req->request->get('date');

        $wantedDay = explode('-',$data)[2];
        $wantedMonth = explode('-',$data)[1];
        $year = explode('-',$data)[0];
        $dataId = intval($wantedDay . $wantedMonth . $year);

        $startHour = $req->request->get('start');
        $sHour = intval(explode(':',$startHour)[0]);
        $sMinute = intval(explode(':',$startHour)[1]);
        $startTime = new DateTime();
        $startTime->setTime($sHour,$sMinute);

        $endHour = $req->request->get('end');
        $eHour = intval(explode(':',$endHour)[0]);
        $eMinute = intval(explode(':',$endHour)[1]);
        $endTime = new DateTime();
        $endTime->setTime($eHour,$eMinute);

        $appointmentDesc = $req->request->get('description');
        $appointmentNote = $req->request->get('note');
        $statId = $req->request->get('status');

        $em = $this->getDoctrine()->getManager();

        $status = $em->getRepository('AppBundle:Status')->find($statId);
        $uid = $session->get('uid');
        $userId = $em->getRepository('AppBundle:User')->find($uid);
        $dateId = $em->getRepository('AppBundle:Dates')->findOneByDataId($dataId);

        $appointment = new Appointment();
        $appointment->setDate($dateId);
        $appointment->setStartHour($startTime);
        $appointment->setEndHour($endTime);
        $appointment->setStatus($status);
        $appointment->setUser($userId);
        $appointment->setDescription($appointmentDesc);
        $appointment->setNote($appointmentNote);

        $em->persist($appointment);
        $em->flush();

        return $this->render('AppBundle:Default:show_user_page.html.twig',[
            'user'=>$session->get('name')
        ]);

    }

    /**
     * @Route("/editAppointment/{id}")
     * @param Request $req
     * @param null $id
     * @return Response|null
     */
    public function editAppointmentAction(Request $req,$id = null)
    {
        return $this->render('AppBundle:Appointment:edit_appointment.html.twig', []);
    }

}
