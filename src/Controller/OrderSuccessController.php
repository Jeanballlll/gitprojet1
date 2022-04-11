<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        
        if (!$order->getIsPaid()) {
            // Vider le panier utilisateur "cart"
            $cart->remove();

            // Quand la commande a été payé, modifier isPaid en mettant la valeur 1
            $order->setIsPaid(1);
            $this->entityManager->flush();
    
        
        // Envoyer un email de confirmation au client
                $mail = new Mail();
                $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Merci pour votre commande.<br/><br/>La premiere Boutique Française.";
                $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande à bien été validée.', $content);
            } 
    
        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
