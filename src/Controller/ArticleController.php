<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    /**
     * home page with all articles
     * @param ArticleRepository $repository
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $repository): Response
    {
        $articles = $repository->findAll();
        return $this->render('article/index.html.twig', ["articles" => $articles]);
    }

    /**
     * one article
     * @param int $idArticle
     * @param ArticleRepository $repository
     * @return Response
     */
    #[Route('/article/{idArticle}', name: 'article_one')]
    public function oneArticle(int $idArticle, ArticleRepository $repository): Response {

        $article = $repository->find($idArticle);
        return $this->render('article/one.html.twig', ["article" => $article]);
    }

    /**
     * add article
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/article/add', name: 'article_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        $submittedToken = $request->request->get("csrf_token");

        if ($this->isCsrfTokenValid("article-add", $submittedToken)) {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();
                $message = $translator->trans('Article added successfully');
                $this->addFlash("success", $message);
                return $this->redirectToRoute("home");
            }
        }
        return $this->render('article/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * update article
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/article/update/{id}', name: 'article_update')]
    public function update(Article $article, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response {

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $message = $translator->trans('Article modified successfully');
            $this->addFlash("success", $message);
            $id = $article->getId();
            return $this->redirect("/article/$id");
        }
        return $this->render('article/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * delete article
     * @param Article $article
     * @param EntityManagerInterface $entityManager
     * @param ArticleRepository $repository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[Route('/article/delete/{id}', name: 'article_delete')]
    public function delete(Article $article, EntityManagerInterface $entityManager, ArticleRepository $repository, TranslatorInterface $translator): Response {

        $repository->remove($article);
        $message = $translator->trans('Article deleted successfully');
        $this->addFlash("success", $message);
        return $this->redirectToRoute("home");
    }
}
