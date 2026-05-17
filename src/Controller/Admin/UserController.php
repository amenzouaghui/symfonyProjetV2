<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
{
    $users = $userRepository->createQueryBuilder('u')
        ->where('u.roles NOT LIKE :role')
        ->setParameter('role', '%ROLE_ADMIN%')
        ->getQuery()
        ->getResult();

    return $this->render('admin/user/index.html.twig', [
        'users' => $users,
    ]);
}

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle', name: 'app_admin_user_toggle', methods: ['POST'])]
    public function toggle(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(!$user->isActive());
            $entityManager->flush();
            $this->addFlash('success', 'Statut du compte modifié !');
        }
        return $this->redirectToRoute('app_admin_user_index');
}
}
