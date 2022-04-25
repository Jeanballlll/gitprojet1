<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mot-de-passe-oublie/password', name: 'app_reset_password')]
    public function index(Request $request): Response
    {

        if ($this->getUser()) {
        return $this->redirectToRoute('home');
    }

        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {
                // 1 : Enregistrer en dbb la demande de reset_password avec user, token, createAt.
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTimeImmutable());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // 2 : Envoyer un lien à l'utilisateur pour mettre à jour son mot de passe

                $url = $this->generateUrl('app_update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname()."<br>Vous avez demandé à réinitialiser votre mot de passe sur la Boutique J-C.B<br><br>";
                $content .= "Merci de clicquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe', $content);
                $this->addFlash('notice', 'Vous allez recevoir un mail de réinitialisation du mot de passe.');
            } else {

                $this->addFlash('notice', 'Cette adresse email est inconnue.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/modifier-mot-de-passe/{token}', name: 'app_update_password')]
    public function update(Request $request, $token, UserPasswordHasherInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('app_reset_password');
        }   

            // Vérifier si le createAt = now - 3h
            $now = new DateTimeImmutable();
            if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {

                $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveller.');
                return $this->redirectToRoute('reset_password');
                
            }

            // Rendre une vue avec mot de passe et confirmation 
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $new_pwd = $form->get('new_password')->getData();

                //Encodage des mots de passe
                $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);
                $reset_password->getUser()->setPassword($password);

                //Flush en base de donnée
                $this->entityManager->flush();

                // $user->setPassword($password);
                // $this->entityManager->persist($user);
                // $this->entityManager->flush();

                //Redirection de l'utilisateur vers la page de connexion
                $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');
                return $this->redirectToRoute('app_login');

                

            }

            return $this->render('reset_password/update.html.twig', [
                'form' => $form->createView()
            ]);
            
       
    }
}
