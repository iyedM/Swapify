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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route('/blogs', name: 'app_blog_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $acceptedBlogs = $entityManager->getRepository(Blog::class)->findBy(
            ['statut' => EtatEnum::Acceptée],
            ['id' => 'DESC'] // Order by id in descending order
        );
    
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
        
        // If the logged-in user is an admin, set the blog's status to accepted right away
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $blog->setStatut(EtatEnum::Acceptée); // Automatically set the status to "accepted"
        }
        
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
        // Ensure the user is either the blog owner or an admin
        $this->denyAccessUnlessGranted('ROLE_USER');  // Basic check for regular users
    
        // If the user is an admin, automatically accept the blog
        if ($this->isGranted('ROLE_ADMIN')) {
            $blog->setStatut(EtatEnum::Acceptée);  // Admin automatically accepts the blog
        } else {
            // Ensure only the blog owner can access the edit page
            if ($blog->getUser() !== $this->getUser()) {
                throw $this->createAccessDeniedException('You are not allowed to edit this blog.');
            }
            
            // Set the blog status to 'enAttente' when it is edited by a regular user
            $blog->setStatut(EtatEnum::enAttente);  // Set status to "pending" for approval by admin
        }
    
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
        
            // Redirect to the index page or any other page you wish after updating
            $this->addFlash('success', 'Blog updated successfully!');
            return $this->redirectToRoute('app_blog_index');
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
    
    #[Route('/admin/pending-blogs', name: 'admin_pending_blogs')]
    public function showPendingBlogs(BlogRepository $blogRepository)
    {
        // Find all blogs with the status "enAttente"
        $blogs = $blogRepository->findBy(['statut' => EtatEnum::enAttente]);

        return $this->render('admin/pending_blogs.html.twig', [
            'blogs' => $blogs,
        ]);
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
