<?php

namespace App\Controller\Admin;

use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        CommandeRepository $commandeRepo,
        UserRepository $userRepo,
        MeubleRepository $meubleRepo
    ): Response {
        // Nombre de clients
        $nbClients = $userRepo->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();

        // Chiffre d'affaires total
        $ca = $commandeRepo->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre de commandes
        $nbCommandes = $commandeRepo->count([]);

        // Nombre de meubles
        $nbMeubles = $meubleRepo->count([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'nbClients'   => $nbClients,
            'ca'          => $ca ?? 0,
            'nbCommandes' => $nbCommandes,
            'nbMeubles'   => $nbMeubles,
        ]);
    }
}