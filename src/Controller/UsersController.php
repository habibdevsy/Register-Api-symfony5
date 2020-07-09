<?php
namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
class UsersController extends AbstractController
{
    

/**
     * @Route("/", name="api_home")
     */
    public function home()
    {
        return $this->json(['result' => true]);
    }
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
         {
             $this->passwordEncoder = $passwordEncoder;
        }
    

    /**
     * @Route("/Users",name="createUser",methods={"POST"})
     */
    public function Create( Request $request){
        $entityManager  = $this->getDoctrine()->getManager();
        $user = new Users();

        $userName        = $request->request->get("user_name");
        $email           = $request->request->get("email");
        $password        = $request->request->get("password");
        $passwordConfirm = $request->request->get("passwordConfirm");
        
         //Array errors
         $errors = [];

         //Check if password does not match the confirmation
         if($password != $passwordConfirm)
         {
             $errors[] = "Password does not match the password confirmation!!";
         }
         // password is at least 8 characters long?
         if(strlen($password) < 8)
         {
             $errors[] = "Password should be at least 8 characters!!";
         }
         if(!is_string($userName)){
           $usernameString = strval($userName);
         }
         if(!$errors)
         {
           
             
             $user->setUserName($usernameString);
             $user->setEmail($email);
             $user->setPassword($this->passwordEncoder->encodePassword(
                 $user,
                 $password));
 
             try
             {
                 //Save new user
                 $entityManager ->persist($user);
                 $entityManager ->flush();
 
                 //return the User object as json
                 return $this->json([
                     'user' => $this->getUser()
                 ],
                     200,
                     [],
                     [
                         'groups' => ['api']
                     ]
                 );
             }
             catch(UniqueConstraintViolationException $e)
             {
                 $errors[] = "The email already has an register!";
             }
             catch(\Exception $e)
             {
                 $errors[] = "Unable to save new user.";
             }
 
            }
        return $this->json([
                'errors' => $errors
            ], 400);
    }
}