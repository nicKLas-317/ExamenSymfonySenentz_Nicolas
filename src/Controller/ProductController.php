<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class ProductController extends AbstractController
{

    /**
     * @Route("admin/products", name="produits")
     */
    public function index(ProductRepository $productRepository): Response
    {
        // REstrictions Users
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/product/{id}-{slug}", name="detailProduit")
     */
    public function detailProduct(EntityManagerInterface $em, $id): Response
    {
        // NO REstrictions Users : all users can see this page
    
        $product = $em->getRepository(Product::class)->find($id);

        return $this->render('product/productDetail.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("admin/product/add",name="ajoutProduct")
     */
    public function addProduct(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // REstrictions Users
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $product = new Product;
        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($slugger->slug($product->getNom()));
            $idCategory = $form['category']->getData()->getId();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('produits', ['id' => $idCategory]);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("admin/product/edit/{id}", name="editProduct")
     */
    public function editProduct(Request $request, EntityManagerInterface $em, $id): Response
    {
        // REstrictions Users
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $product = $em->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Modification bien prise en compte');
            return $this->redirectToRoute('produits');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("admin/product/delete/{id}", name= "deleteProduct")
     */
    public function deleteProduct(EntityManagerInterface $em, $id)
    {
        // REstrictions Users
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $product = $em->getRepository(Product::class)->find($id);

        $em->remove($product);
        $em->flush();
        $this->addFlash('success', 'Le produit a bien été supprimé ;)');

        return $this->redirectToRoute('produits');
    }
}
