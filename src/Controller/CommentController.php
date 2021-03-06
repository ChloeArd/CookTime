<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CommentController extends AbstractController
{
    /** Add a comment
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param UserRepository $repository
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[Route('/comment/add/{slug}', name: 'comment_add')]
    public function add(Article $article, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, UserRepository $repository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        $submittedToken = $request->request->get("csrf_token");

        if ($this->isCsrfTokenValid("comment-add", $submittedToken)) {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($comment);
                $comment->setArticle($article);
                date_default_timezone_set('Europe/Paris');
                $datetime = new \DateTime();
                $comment->setDate($datetime);
                // Retrieve logged in user ID
                $idUser = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
                $userAuthenticated = $repository->find($idUser);
                $comment->setUser($userAuthenticated);
                $entityManager->flush();

                $message = $translator->trans('Comment added successfully');
                $this->addFlash("success", $message);
                $slug = $article->getSlug();
                return $this->redirect("/article/$slug");
            }
        }
        return $this->render('comment/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Update a comment
     * @param Comment $comment
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/comment/update/{id<\d+>}', name: 'comment_update')]
    public function update(Comment $comment, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $message = $translator->trans('Comment modified successfully');
            $this->addFlash("success", $message);
            $slug = $comment->getArticle()->getSlug();
            return $this->redirect("/article/$slug");
        }
        return $this->render('comment/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Delete a comment
     * @param Comment $comment
     * @param CommentRepository $repository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[Route('/comment/delete/{id<\d+>}', name: 'comment_delete')]
    public function delete(Comment $comment, CommentRepository $repository, TranslatorInterface $translator): Response
    {
        $repository->remove($comment);
        $message = $translator->trans('Comment deleted successfully');
        $this->addFlash("success", $message);
        $slug = $comment->getArticle()->getSlug();
        return $this->redirect("/article/$slug");
    }
}
