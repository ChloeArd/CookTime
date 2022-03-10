<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    /**
     * Account of user
     * @return Response
     */
    #[Route('/account', name: 'account')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * Update user
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/user/update/{id<\d+>}', name: 'user_update')]
    public function update(User $user, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $message = $translator->trans('Your account modified successfully');
            $this->addFlash("success", $message);
            return $this->redirectToRoute("account");
        }
        return $this->render('user/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Delete user
     * @param User $user
     * @param UserRepository $repository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    #[Route('/user/delete/{id<\d+>}', name: 'user_delete')]
    public function delete(User $user, UserRepository $repository, TranslatorInterface $translator): Response
    {
        $repository->remove($user);
        $message = $translator->trans('User deleted successfully');
        $this->addFlash("success", $message);
        return $this->redirectToRoute("home");
    }
}
