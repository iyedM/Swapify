<?php

namespace App\Controller;

use App\Enum\EtatEnum;
use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route('/blogs', name: 'app_blog_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $acceptedBlogs = $entityManager->getRepository(Blog::class)->findBy(['statut' => EtatEnum::Acceptée]);
    
        return $this->render('blog/index.html.twig', [
            'blogs' => $acceptedBlogs,
        ]);
    }
    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
    
        $blog = new Blog();
        $blog->setUser($this->getUser());
    
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                // Move the file to the directory where images are stored
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
    
                // Update the 'image' property to store the file name
                $blog->setImage($newFilename);
            }
    
            $entityManager->persist($blog);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        // Ensure only the blog owner can access the edit page
        if ($blog->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this blog.');
        }
    
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        // Ensure only the blog owner can delete the blog
        if ($blog->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this blog.');
        }
    
        if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->getPayload()->getString('_token'))) {
            // Remove related comments first
            foreach ($blog->getListeCommentaires() as $comment) {
                $entityManager->remove($comment);
            }
    
            // Remove the blog
            $entityManager->remove($blog);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/accept/{id}', name: 'accept_blog', methods: ['GET'])]
    public function acceptBlog(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Find the blog by its ID
        $blog = $entityManager->getRepository(Blog::class)->find($id);
        
        if (!$blog) {
            // If the blog is not found, show an error message
            $this->addFlash('error', 'Blog not found!');
            return $this->redirectToRoute('app_blog_index');
        }
        
        // Check if the blog is already accepted
        if ($blog->getStatut() === EtatEnum::Acceptée) {
            $this->addFlash('info', 'This blog is already accepted.');
            return $this->redirectToRoute('app_blog_index'); // Redirect to index to show flash message
        }
        
        // Update the blog's statut to 'Acceptée'
        $blog->setStatut(EtatEnum::Acceptée);
        $entityManager->flush();
        
        // Add a success message to the flash bag
        $this->addFlash('success', 'Blog accepted successfully!');
        
        // Redirect back to the index page
        return $this->redirectToRoute('app_blog_index');
    }
    #[Route('/reject/{id}', name: 'reject_blog', methods: ['GET'])]
public function rejectBlog(int $id, EntityManagerInterface $entityManager): RedirectResponse
{
    // Find the blog by its ID
    $blog = $entityManager->getRepository(Blog::class)->find($id);
    
    if (!$blog) {
        // If the blog is not found, show an error message
        $this->addFlash('error', 'Blog not found!');
        return $this->redirectToRoute('app_blog_index');
    }
    
    // Check if the blog is already rejected
    if ($blog->getStatut() === EtatEnum::Rejetée) {
        $this->addFlash('info', 'This blog is already rejected.');
        return $this->redirectToRoute('app_blog_index'); // Redirect to index to show flash message
    }
    
    // Update the blog's statut to 'Rejetée'
    $blog->setStatut(EtatEnum::Rejetée);
    $entityManager->flush();
    
    // Add a success message to the flash bag
    $this->addFlash('success', 'Blog rejected successfully!');
    
    // Redirect back to the index page
    return $this->redirectToRoute('app_blog_index');
}

#[Route('/rate/{id}', name: 'app_blog_rate', methods: ['POST'])]
public function rateBlog(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
{
    $rating = (int) $request->request->get('rating');

    if ($rating < 0 || $rating > 5) {
        $this->addFlash('error', 'Invalid rating. Please select a value between 0 and 5.');
        return $this->redirectToRoute('app_blog_index');
    }

    $blog->addRate($rating);
    $entityManager->flush();

    $this->addFlash('success', 'Rating added successfully!');
    return $this->redirectToRoute('app_blog_index');
}

//to complete this together with the average rating
#[Route('/approve', name: 'app_blog_approve', methods: ['GET'])]
public function approve(EntityManagerInterface $entityManager): Response
{
    $pendingBlogs = $entityManager->getRepository(Blog::class)->findBy(['statut' => EtatEnum::enAttente]);

    return $this->render('blog/approve.html.twig', [
        'blogs' => $pendingBlogs,
    ]);
}
    
#[Route('/my-blogs', name: 'app_blog_my_blogs', methods: ['GET'])]
public function myBlogs(EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    $user = $this->getUser();

    $blogs = $entityManager->getRepository(Blog::class)
        ->createQueryBuilder('b')
        ->where('b.user = :user')
        ->andWhere('b.statut = :statut')
        ->setParameter('user', $user)
        ->setParameter('statut', EtatEnum::Acceptée)
        ->getQuery()
        ->getResult();

    return $this->render('blog/myblogs.html.twig', [
        'blogs' => $blogs,
    ]);
}

}
