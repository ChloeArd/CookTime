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
        $originalFilename = $user->getAvatar();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$form->get('avatar')->isEmpty()) {

                if ($originalFilename !== null) {
                    //delete a original picture
                    unlink($this->getParameter('avatar_directory') . "/" . $originalFilename);
                }

                //upload a new picture
                $filePicture = $form->get('avatar')->getData();
                $filename = uniqid() . "." . $filePicture->guessExtension();

                $filePicture->move(
                    $this->getParameter('avatar_directory'),
                    $filename
                );

                $user->setAvatar($filename);
            }

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
        //delete a picture
        $filePicture = $user->getAvatar();

        if ($this->getParameter('avatar_directory') . "/" . $filePicture) {
            unlink($this->getParameter('avatar_directory') . "/" . $filePicture);
        }

        $repository->remove($user);
        $message = $translator->trans('User deleted successfully');
        $this->addFlash("success", $message);
        return $this->redirectToRoute("home");
    }
}
