<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Department;
use AppBundle\Entity\Status;
use AppBundle\Entity\Type;
use AppBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use PDOException;
use Exception;

class AdminController extends Controller
{
    /**
     * @Route("/admin")
     */
    public function adminAction()
    {
        return $this->render('AppBundle:Admin:adminpannel.html.twig', []);
    }


    /**
     * @Route("/admin/listUsers")
     */
    public function listUsersAction()
    {
        $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $users = $userRepo->findAll();

        return $this->render('AppBundle:Admin:list_users.html.twig', [
            'users'=>$users
        ]);
    }

    /**
     * @Route("/admin/addUser", methods={"GET"})
     */
    public function addUserAction()
    {
        $departmentRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Department');
        $departments = $departmentRepo->findAll();

        $typesRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Type');
        $types = $typesRepo->findAll();

        return $this->render('AppBundle:Admin:add_user.html.twig', [
            'departments'=>$departments,
            'types'=>$types
        ]);
    }

    /**
     * @Route("/admin/saveUser/{id}",methods={"POST"})
     */
    public function saveUserAction(Request $request, $id = null)
    {
        $name = $request->request->get('name');
        $type = $request->request->get('type_id');
        $department = $request->request->get('department_id');
        $email =$request->request->get('email');
        $password = $request->request->get('password');
        $checkPass = $request->request->get('checkPassword');

        $departmentRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Department');
        $departments = $departmentRepo->findAll();

        $typesRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Type');
        $types = $typesRepo->findAll();

        $errors = [];

        if(trim($name) == '') {
            $errors[] = 'Please enter a valid name!';
        }

        if(!is_numeric($type)) {
            $errors[] = 'User type not selected';
        }
        if(!is_numeric($department)) {
            $errors[] = 'Department not selected';
        }
        if(trim($email) == '') {
            $errors[] = 'Please enter a valid email!';
        }
        if(trim($password) == '') {
            $errors[] = 'Please enter a valid password!';
        }
        if($password !== $checkPass) {
            $errors[] = 'Password doesn\' match!';
        }

        if(empty($errors)) {
            if(is_numeric($id)) {
                $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($id);
            } else {
                $user = new User();
            }
            $typeId = $typesRepo->find($type);
            $departmentId = $departmentRepo->find($department);

            $user->setType($typeId);
            $user->setDepartment($departmentId);
            $user->setUserName($name);
            $user->setEmail($email);
            $user->setPassword(md5($password));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_admin_listusers');

        } else {

            $types = $typesRepo->findAll();
            $departments = $departmentRepo->findAll();
            return $this->render('AppBundle:Admin:add_user.html.twig', [
                'types'=>$types,
                'departments'=>$departments,
                'errors'=>$errors,
                'users' => $request->request->all()
            ]);
        }
    }

    /**
     * @Route("/admin/showUser/{id}")
     */
    public function showUserAction($id)
    {
        return $this->render('AppBundle:Admin:show_user.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/admin/editUser/{id}", methods={"GET"})
     */
    public function editUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $typeRepo = $em->getRepository('AppBundle:Type');
        $types = $typeRepo->findAll();
        $departmentRepo = $em->getRepository('AppBundle:Department');
        $departments = $departmentRepo->findAll();
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->findOneByid($id);

        return $this->render('AppBundle:Admin:edit_user.html.twig', [
            'user'=>$user,
            'types'=>$types,
            'departments'=>$departments,
        ]);

    }

    /**
     * @Route("/admin/deleteUser/{id}")
     */
    public function deleteUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $em->remove($userRepo->findOneById($id));
        $em->flush();

        return $this->redirectToRoute('app_admin_listusers');
    }

    /**
     * @Route("/admin/listType")
     */
    public function listTypeAction()
    {
        $mess = [];
        $typeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Type');
        $types = $typeRepo->findAll();

        return $this->render('AppBundle:Admin:list_type.html.twig', [
            'types'=>$types,
            'message'=>implode(',', $mess)
        ]);
    }

    /**
     * @Route("/admin/addType", methods={"GET"})
     */
    public function addTypeAction()
    {
        return $this->render('AppBundle:Admin:add_type.html.twig', []);
    }

    /**
     * @Route("/admin/saveType/{id}", methods={"POST"})
     */
    public function saveTypeAction(Request $request, $id = null)
    {
        $description = $request->request->get('description');
        $type = $request->request->get('type');

        $errors = [];

        if(trim($description) === '') {
            $errors[] = 'Please enter a valid name!';
        }
        if(trim($type) === '') {
            $errors[] = 'Please enter a valid type!';
        }
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('AppBundle:Type')->findAll();
        for ($i = 0; $i < count($types); $i ++){
            if ($type === $types[$i]->getType()){
                $errors[] = 'This type of user already exists!';
            }
        }

        if(empty($errors)) {
            if (is_numeric($id)){
                $tip = $em->getRepository('AppBundle:Type')->find($id);

            }else{
                $tip = new Type();
            }

            $tip->setDescription($description);
            $tip->setType($type);

            $em->persist($tip);
            $em->flush();

            return $this->redirectToRoute('app_admin_listtype');

        } else {

            return $this->render('AppBundle:Admin:add_type.html.twig', [
                'errors'=>$errors,
                'type' => $request->request->all()
            ]);
        }
    }

    /**
     * @Route("/admin/editType/{id}", methods={"GET"})
     */
    public function editTypeAction($id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $typeRepo = $em->getRepository('AppBundle:Type');
        $type = $typeRepo->findOneById($id);
        return $this->render('AppBundle:Admin:edit_type.html.twig', [
            'type'=>$type
        ]);
    }

    /**
     * @Route("/admin/deleteType/{id}", methods={"GET"})
     */
    public function deleteTypeAction($id = null)
    {
        $mess = [];
        $em = $this->getDoctrine()->getManager();
        $typeRepo = $em->getRepository('AppBundle:Type');
        $types = $typeRepo->findAll();

        try {
            $em->remove($typeRepo->findOneById($id));
            $em->flush();
            // $mess[] = 'Type deleted';

        }catch (DBALException $e) {
            //$message = sprintf('DBALException [%i]: %s', $e->getCode(), $e->getMessage());
            $mess[] = 'You have users of this type in db';
        }

        return $this->render('AppBundle:Admin:list_type.html.twig', [
            'types'=>$types,
            'message'=>implode(',', $mess)
        ]);
    }

    /**
     * @Route("/admin/listDepartments")
     */
    public function listDepartmentsAction()
    {
        $mess = [];
        $departmentRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Department');
        $departments = $departmentRepo->findAll();

        return $this->render('AppBundle:Admin:list_departments.html.twig', [
            'departments'=>$departments,
            'message'=>implode(',', $mess)
        ]);
    }

    /**
     * @Route("/admin/addDepartment", methods={"GET"})
     */
    public function addDepartmentAction()
    {
        return $this->render('AppBundle:Admin:add_department.html.twig', []);
    }

    /**
     * @Route("/admin/saveDepartment/{id}", methods={"POST"})
     */
    public function saveDepartmentAction(Request $request, $id = null)
    {
        $description = $request->request->get('description');

        $errors = [];

        if(trim($description) === '') {
            $errors[] = 'Please enter a valid name!';
        }
        $em = $this->getDoctrine()->getManager();
        $departments = $em->getRepository('AppBundle:Department')->findAll();
        for ($i = 0; $i<count($departments);$i++){
            if ($description === $departments[$i]->getDescription()){
                $errors[] = 'Department already exists';
            }
        }

        if(empty($errors)) {
            if (is_numeric($id)){
                $department = $em->getRepository('AppBundle:Department')
                    ->find($id);

            }else{
                $department = new Department();
            }

            $department->setDescription($description);

            $em->persist($department);
            $em->flush();

            return $this->redirectToRoute('app_admin_listdepartments');

        } else {

            return $this->render('AppBundle:Admin:add_department.html.twig', [
                'errors'=>$errors,
                'departments' => $request->request->all()
            ]);
        }
    }

    /**
     * @Route("/admin/showDepartment/{id}")
     */
    public function showDepartmentAction($id)
    {
        return $this->render('AppBundle:Admin:show_department.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/admin/editDepartment/{id}")
     */
    public function editDepartmentAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $departmentRepo = $em->getRepository('AppBundle:Department');
        $department = $departmentRepo->findOneById($id);
        return $this->render('AppBundle:Admin:edit_department.html.twig', [
            'department'=>$department
        ]);
    }

    /**
     * @Route("/admin/deleteDepartment/{id}", methods={"GET"})
     */
    public function deleteDepartmentAction($id = null)
    {
        $mess = [];
        $em = $this->getDoctrine()->getManager();
        $departmentRepo = $em->getRepository('AppBundle:Department');
        $departments = $departmentRepo->findAll();
        try {
            $em->remove($departmentRepo->findOneById($id));
            $em->flush();
            //$mess[] = 'Department deleted';
        }catch (DBALException $e){
            $mess[] = 'You have users in this department. Please edit users first!';
        }
        return $this->render('AppBundle:Admin:list_departments.html.twig', [
            'departments'=>$departments,
            'message'=>implode(',', $mess)
        ]);
    }

    /**
     * @Route("/admin/listStatus")
     */
    public function listStatusAction()
    {
        $statusRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Status');
        $statuses = $statusRepo->findAll();

        return $this->render('AppBundle:Admin:list_status.html.twig', [
            'statuses'=>$statuses
        ]);
    }

    /**
     * @Route("/admin/addStatus", methods={"GET"})
     */
    public function addStatusAction()
    {
        return $this->render('AppBundle:Admin:add_status.html.twig', []);
    }

    /**
     * @Route("/admin/saveStatus/{id}", methods={"POST"})
     */
    public function saveStatusAction(Request $request, $id = null)
    {
        $description = $request->request->get('description');

        $errors = [];

        if(trim($description) === '') {
            $errors[] = 'Please enter a valid status!';
        }
        $em = $this->getDoctrine()->getManager();
        $statuses = $em->getRepository('AppBundle:Status')->findAll();
        for ($i = 0; $i < count($statuses); $i ++){
            if ($description === $statuses[$i]->getDescription()){
                $errors[] = 'Status already exists';
            }
        }

        if(empty($errors)) {
            if (is_numeric($id)){
                $status = $em->getRepository('AppBundle:Status')->find($id);
            }else{
                $status = new Status();
            }

            $status->setDescription($description);

            $em->persist($status);
            $em->flush();

            return $this->redirectToRoute('app_admin_liststatus');

        } else {

            return $this->render('AppBundle:Admin:add_status.html.twig', [
                'errors'=>$errors,
                'statuses' => $request->request->all()
            ]);
        }
    }

    /**
     * @Route("/admin/showStatus/{id}")
     */
    public function showStatusAction($id)
    {
        return $this->render('AppBundle:Admin:show_status.html.twig', []);
    }

    /**
     * @Route("/admin/editStatus/{id}", methods={"GET"})
     */
    public function editStatusAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $statusRepo = $em->getRepository('AppBundle:Status');
        $status = $statusRepo->findOneById($id);
        return $this->render('AppBundle:Admin:edit_status.html.twig', [
            'status'=>$status
        ]);
    }

    /**
     * @Route("/admin/deleteStatus/{id}", methods={"GET"})
     */
    public function deleteStatusAction($id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $statusRepo = $em->getRepository('AppBundle:Status');
        $em->remove($statusRepo->findOneById($id));
        $em->flush();

        return $this->redirectToRoute('app_admin_liststatus');
    }

}
