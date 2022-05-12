<?php

namespace App\Controller;

use App\Classe\Search;
use App\Form\SearchType;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager; 
    } 


    #[Route('/nos-produits', name: 'app_products')]

    public function index(Request $request): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $products = $this->entityManager->getRepository(Product::class)->findWithSeach($search);
           
        }
        
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]); 
    }
    // {slug} c'est pour que l'utilisateur trouve sa recherche dans l'url.

    #[Route('/produit/{id}', name: 'app_product')]
    public function show($id)
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(1);
        
        if (!$product) {
            return $this->redirectToRoute('app_products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]); 
    }
}
