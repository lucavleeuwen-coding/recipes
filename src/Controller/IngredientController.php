<?php

namespace App\Controller;

use App\Entity\ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ingredient')]
class IngredientController extends AbstractController
{
    #[Route('/', name: 'ingredient_index', methods: ['GET'])]
    public function index(IngredientRepository $ingredientRepository): Response
    {
        if ($this->getUser() == null){
            return $this->redirect('/login');
        }
        if ($this->getUser()->isVerified() == false){
            $this->addFlash('warning', 'Uw dient eerst uw email te verifieren om naar deze pagina te kunnen! ');
        }
        $user = $this->getUser();
        if ($user->isVerified()) {
            return $this->render('ingredient/index.html.twig', [
                'ingredients' => $ingredientRepository->findAll(),
            ]);
        }
        return $this->redirect('/login');
    }
//<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
//<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
//<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    #[Route('/new', name: 'ingredient_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $ingredient = new ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()&& $user->isVerified()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingredient);
            $entityManager->flush();
            $this->addFlash('succes', 'Uw ingredient is succesvol toegevoegd aan de receptenlijst! ');


            return $this->redirectToRoute('ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ingredient/new.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'ingredient_show', methods: ['GET'])]
    public function show(ingredient $ingredient): Response
    {
        $user = $this->getUser();
        if ($user->isVerified()){
            return $this->render('ingredient/show.html.twig', [
                'ingredient' => $ingredient,
            ]);
        }
        return $this->redirect('/login');
    }

    #[Route('/{id}/edit', name: 'ingredient_edit', methods: ['GET','POST'])]
    public function edit(Request $request, ingredient $ingredient): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('succes', 'Uw ingredient is succesvol gewijzigd! ');

            return $this->redirectToRoute('ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'ingredient_delete', methods: ['POST'])]
    public function delete(Request $request, ingredient $ingredient): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete'.$ingredient->getId(), $request->request->get('_token'))&& $user->isVerified()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ingredient);
            $entityManager->flush();
            $this->addFlash('succes', 'Uw ingredient is succesvol verwijderd! ');
        }

        return $this->redirectToRoute('ingredient_index', [], Response::HTTP_SEE_OTHER);
    }
}
