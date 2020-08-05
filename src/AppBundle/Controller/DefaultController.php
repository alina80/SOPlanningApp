<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Dates;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function homeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $usersRepo = $em->getRepository('AppBundle:User');
        $users = $usersRepo->findAll();
        return $this->render('AppBundle:Default:home.html.twig',[
            'success'=>true
        ]);

    }

    /**
     * @Route("/login", methods={"POST"})
     */
    public function loginAction(SessionInterface $session, Request $req)
    {

        //$name = $req->request->get('name');
        $email = $req->request->get('email');
        $password = $req->request->get('password');


        $em = $this->getDoctrine()->getManager();
        $usersRepo = $em->getRepository('AppBundle:User');
        $user = $usersRepo->findOneByEmail($email);

        $match = false;
        if ($user){
            $match = $user->getPassword() == md5($password);
        }

        $messages = [];
        $messages['success'] = $match;

        $today = (new \DateTime('now'))->getTimestamp();
        $day = date('d-m-Y', $today);
        $dayArr = explode('-',$day);
        $zi = $dayArr[0];
        $luna = $dayArr[1];
        $an = $dayArr[2];
        $dId = intval($zi . $luna . $an);

        if ($match){
            $session->set('uid',$user->getId());
            $session->set('name', $user->getUserName());
            $session->set('day', $day);
            $session->set('did', $dId);
            return $this->redirectToRoute('app_default_showuserpage');
        }
        return $this->render('AppBundle:Default:home.html.twig', $messages);

    }

    /**
     * @Route("/logout")
     */
    public function logoutAction(SessionInterface $session)
    {
        $session->clear();
        return $this->render('@App/Default/show_user_page.html.twig');
    }

    /**
     * @Route("/showUserPage")
     */
    public function showUserPageAction(SessionInterface $session)
    {

        print_r($_SESSION);
        return $this->render('AppBundle:Default:show_user_page.html.twig',[
            'user'=>$session->get('name')
        ]);

    }

    /**
     * @Route("/selectDate/{id}", methods={"POST"})
     */
    public function selectDateAction(SessionInterface $session, Request $request, $id =null)
    {
        /*      $data = $request->request->get('data')->format('Y/m/d');
                $day = explode('/',$data)[2];
                $month = explode('/',$data)[1];
                $year = explode('/',$data)[0];
        */
        $day = $request->request->get('day');
        $month = $request->request->get('month');
        $year = $request->request->get('year');

        $data = new \DateTime();
        $data->setDate($year,$month,$day);

        $wantedData = $data->format('Y-m-d');
        $wantedDay = explode('-',$wantedData)[2];
        $wantedMonth = explode('-',$wantedData)[1];
        $dataId = intval($wantedDay . $wantedMonth . $year);

        $em = $this->getDoctrine()->getManager();
        $dates = $em->getRepository('AppBundle:Dates')->findAll();

        $dataFound = [];
        for ($i = 0; $i < count($dates); $i ++){
            if ($data->format('Y-m-d') === $dates[$i]->getData()->format('Y-m-d')){
                $dataFound[$i] = $dates[$i]->getDataId();
            }
        }

        if (!empty($dataFound)){
            $date = $em->getRepository('AppBundle:Dates')->findOneByDataId($dataId);
        }else {
            $date = new Dates();
        }

        $date->setData($data);
        $date->setDay(intval($day));
        $date->setMonth(intval($month));
        $date->setYear($year);
        $date->setDataId($dataId);

        $em->persist($date);
        $em->flush();

        return $this->render('AppBundle:Default:show_user_page.html.twig',[
            'user'=>$session->get('name')
        ]);
    }
}
