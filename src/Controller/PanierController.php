<?php
namespace App\Controller;

use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    // ✅ Voir le panier
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, MeubleRepository $meubleRepo): Response
    {
        $panier = $session->get('panier', []);
        $panierData = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $meuble = $meubleRepo->find($id);
            if ($meuble) {
                $sousTotal = $meuble->getPrix() * (int) $quantite;
                $total += $sousTotal;
                $panierData[] = [
                    'meuble'    => $meuble,
                    'quantite'  => (int) $quantite,
                    'sousTotal' => $sousTotal,
                ];
            }
        }

        return $this->render('panier/index.html.twig', [
            'panierData' => $panierData,
            'total'      => $total,
        ]);
    }

    // ✅ Ajouter au panier
    #[Route('/panier/ajouter/{id}', name: 'app_panier_ajouter')]
    public function ajouter(int $id, Request $request, SessionInterface $session, MeubleRepository $meubleRepo): Response
    {
        $meuble = $meubleRepo->find($id);
        if (!$meuble) {
            throw $this->createNotFoundException('Meuble introuvable');
        }

        $panier = $session->get('panier', []);
        $quantite = (int) $request->request->get('quantite', 1);

        if (isset($panier[$id])) {
            $panier[$id] += $quantite;
        } else {
            $panier[$id] = $quantite;
        }

        $session->set('panier', $panier);
        $this->addFlash('success', '✅ ' . $meuble->getNom() . ' ajouté au panier !');

        return $this->redirectToRoute('app_panier');
    }

    // ✅ Modifier quantité
    #[Route('/panier/modifier/{id}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifier(int $id, Request $request, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        $quantite = (int) $request->request->get('quantite', 1);

        if ($quantite <= 0) {
            unset($panier[$id]);
        } else {
            $panier[$id] = $quantite;
        }

        $session->set('panier', $panier);
        return $this->redirectToRoute('app_panier');
    }

    // ✅ Supprimer un item
    #[Route('/panier/supprimer/{id}', name: 'app_panier_supprimer')]
    public function supprimer(int $id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        unset($panier[$id]);
        $session->set('panier', $panier);

        $this->addFlash('success', 'Article supprimé du panier.');
        return $this->redirectToRoute('app_panier');
    }

    // ✅ Vider le panier
    #[Route('/panier/vider', name: 'app_panier_vider')]
    public function vider(SessionInterface $session): Response
    {
        $session->remove('panier');
        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_panier');
    }
}