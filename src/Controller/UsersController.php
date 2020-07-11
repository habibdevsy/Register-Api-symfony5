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
         //Is user name already exists?
         
         if ($this->getDoctrine()->getRepository(Users ::class)->findUserName($userName)) {
            
            $errors='Unable to create user name,'.' '.$userName .' '. 'already exists!';
         
        }
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
    
    
     /**
      * @Route("/login",name="login",methods={"POST"})
      */
    public function login( Request $request){
        
   
       
        $entityManager  = $this->getDoctrine()->getManager();

        $userName        = $request->request->get("username");
        $email           = $request->request->get("email");
        $password        = $request->request->get("password");
        
        //Is User Name Found
        $IsUserNameFound =$this->getDoctrine()->getRepository(Users ::class)->loadUserByUsername($userName);
        $passwordInDB="";
        
        $errors = [];
         //Check if user name not found
         if(!$IsUserNameFound){
            $errors[] = "the"." ".$userName." "."not found!!";
         }
         //Password matching the username in the database
         if($IsUserNameFound){
            $passwordInDB = $IsUserNameFound->getPassword();
         }
         //Check if password not found
         if($password != $passwordInDB && $passwordInDB!="")
         {
             $errors[] = "Password Wronge!!";
         }

         //It's ok
         if(!$errors)
         {
            return new Response('Welcome user '. $IsUserNameFound->getUserName());
         }

         return $this->json([
            'errors' => $errors
        ], 400);
    }
}
