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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\Json;

class UsersController extends AbstractController
{
    

    /**
     * @Route("/", name="api_home")
     */
    public function home()
    {
    return $this->json(['result' => true]);
}
    

    /**
     * @Route("/register", name="api_register", methods={"POST"})
     */
    public function register( UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        $entityManager  = $this->getDoctrine()->getManager();
        $user = new Users();
       
        $email                  = $request->request->get("email");
        $password               = $request->request->get("password");
        $passwordConfirmation   = $request->request->get("password_confirmation");
       
        $errors = [];
        if($password != $passwordConfirmation)
        {
            $errors[] = "Password does not match the password confirmation.";
        }

        if(strlen($password) < 8)
        {
            $errors[] = "Password should be at least 8 characters.";
        }

        if(!$errors)
        {
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $password);
            
            $user->setEmail($email);
            $user->setPassword($encodedPassword);

            try
            {
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->json([
                    'user' => $user
                ]);
            }
            catch(UniqueConstraintViolationException $e)
            {
                $errors[] = "The email provided already has an account!";
            }
            // catch(\Exception $e)
            // {
            //     $errors[] = "Unable to save new user at this time.";
            // }

        }


        return $this->json([
            'errors' => $errors
        ], 400);

    }
   
   
     /**
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function login()
    {
        return $this->json(['result' => true]);
    }

    
    /**
     * @Route("/profile", name="api_profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile()
    {
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
}
