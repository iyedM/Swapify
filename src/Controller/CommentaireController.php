<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\BlogRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/commentaire/new/{blogId}', name: 'app_commentaire_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $blogId, BlogRepository $blogRepository): Response
    {
        $blog = $blogRepository->find($blogId);
        if (!$blog) {
            throw $this->createNotFoundException("Blog not found.");
        }
    
        $commentaire = new Commentaire();
        $commentaire->setContenuCmt($request->request->get('contenu'));
        $commentaire->setBlog($blog);
        //$commentaire->setUser($this->getUser()); // Set the authenticated user
    
        $entityManager->persist($commentaire);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_blog_index');
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
