<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Classe\Cart;
use Stripe\Checkout\Session;
use Stripe\Stripe;


 

class StripeController extends AbstractController
{
    #[Route('/commande/create-session', name: 'app_stripe_create_session')]
    public function index(Cart $cart): Response
    {
        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
        // Pour avoir les images des achats effectuer,il ne faut pas etre en local (ex:https://projet1.fr)
 
        foreach ($cart->getFull() as $product) {
            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product['product']->getPrice(),
                    'product_data' => [
                        'name' => $product['product']->getName(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product['product']->getIllustration()],
                    ],
                ],
                'quantity' => $product['quantity'],
            ];
        }
 
        Stripe::setApiKey('sk_test_51Kij3tHspDAIhxScl6ZeIwNRiy66LkLezfeCtzznb3uuMjuQHJK8UxGrDSwMkDuzJ0HqrD1aiGlAYaImT25BdrCf00tfIWIOdG');
 
        $checkout_session = Session::create([
            'line_items' => [
                $product_for_stripe
            ],
            'payment_method_types' => [
                'card',
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);
 
        return $this->redirect($checkout_session->url);
    }
}


