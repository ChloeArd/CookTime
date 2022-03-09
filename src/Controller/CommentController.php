<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
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
    #[Route('add/Comment/{id}', name: 'app_comment')]
    public function add(Article $article, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        $submittedToken = $request->request->get("csrf_token");

        if ($this->isCsrfTokenValid("comment-add", $submittedToken)) {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();
                $message = $translator->trans('Comment added successfully');
                $this->addFlash("success", $message);
                $id = $article->getId();
                return $this->redirect("/article/$id");
            }
        }
        return $this->render('comment/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/comment/update/{id}', name: 'comment_update')]
    public function update(Comment $comment, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $message = $translator->trans('Comment modified successfully');
            $this->addFlash("success", $message);
            $id = $comment->getArticle()->getId();
            return $this->redirect("/article/$id");
        }
        return $this->render('comment/update.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete')]
    public function delete(Comment $comment, CommentRepository $repository, TranslatorInterface $translator): Response
    {
        // faire que user ne soit pas supprimer et ni l'article
        $repository->remove($comment);
        $message = $translator->trans('Comment deleted successfully');
        $this->addFlash("success", $message);
        $id = $comment->getArticle()->getId();
        return $this->redirect("/article/$id");
    }
}
