<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\Break_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {

        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // dès que le formulaire est soumis, traite l'information et si c'est valide (RegisterType), enregistre en bdd.
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            //Vérifi si l'utilisateur n'est pas encore présent en bdd
            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            // Un petit msg de notification a l'utisateur, une fois inscrit 
            if (!$search_email)
            {
                $password = $encoder->hashPassword($user,$user->getPassword());

                $user->setPassword($password);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br/>Bienvenue sur la plateform en ligne.<br/><br/>La premiere Boutique Française.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur la Boutique J-C.B', $content);

                
                $notification = "Bienvenue, connectez-vous a votre compte.";
            } else {
                $notification = "Cette adresse e-mail existe déjà.";
            }
  
        }

            return $this->render('register/index.html.twig', [
                'form' => $form->createView(),
                'notification' => $notification
        ]);
    }
}
