<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LigneCommandeRepository;

class CommandeController extends AbstractController
{
    // ✅ Afficher le formulaire de validation
    #[Route('/commande/valider', name: 'app_commande_valider')]
    public function valider(
        SessionInterface $session,
        MeubleRepository $meubleRepo
    ): Response {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            return $this->redirectToRoute('app_panier');
        }

        // Calcul du total
        $total = 0;
        $panierData = [];
        foreach ($panier as $id => $quantite) {
            $meuble = $meubleRepo->find($id);
            if ($meuble) {
                $sousTotal = $meuble->getPrix() * (int) $quantite;
                $total += $sousTotal;
                $panierData[] = [
                    'meuble'   => $meuble,
                    'quantite' => (int) $quantite,
                    'sousTotal'=> $sousTotal,
                ];
            }
        }

        // Génère un code aléatoire à 6 chiffres et le stocke en session
        if (!$session->get('code_validation')) {
            $code = rand(100000, 999999);
            $session->set('code_validation', $code);
        }

        return $this->render('commande/valider.html.twig', [
            'panierData' => $panierData,
            'total'      => $total,
            'code'       => $session->get('code_validation'),
        ]);
    }

    // ✅ Confirmer la commande
    #[Route('/commande/confirmer', name: 'app_commande_confirmer', methods: ['POST'])]
    public function confirmer(
        Request $request,
        SessionInterface $session,
        MeubleRepository $meubleRepo,
        EntityManagerInterface $em
    ): Response {
        $panier = $session->get('panier', []);
        $codeSession = $session->get('code_validation');
        $codeSaisi = $request->request->get('code_validation');

        // ❌ Code incorrect
        if ($codeSaisi != $codeSession) {
            $this->addFlash('error', '❌ Code incorrect ! Réessayez.');
            return $this->redirectToRoute('app_commande_valider');
        }

        // ✅ Code correct — créer la commande
        $total = 0;
        foreach ($panier as $id => $quantite) {
            $meuble = $meubleRepo->find($id);
            if ($meuble) {
                $total += $meuble->getPrix() * (int) $quantite;
            }
        }

        // Créer la commande
        $commande = new Commande();
        $commande->setNumero('CMD-' . uniqid());
        $commande->setStatut('terminee');
        $commande->setTotal($total);
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setUser($this->getUser());

        $em->persist($commande);

        // Créer les lignes de commande
        foreach ($panier as $id => $quantite) {
            $meuble = $meubleRepo->find($id);
            if ($meuble) {
                $ligne = new LigneCommande();
                $ligne->setCommande($commande);
                $ligne->setMeuble($meuble);
                $ligne->setNomMeuble($meuble->getNom());
                $ligne->setQuantite((int) $quantite);
                $ligne->setPrixUnitaire($meuble->getPrix());
                $em->persist($ligne);
            }
        }

        $em->flush();

        // Vider le panier et le code
        $session->remove('panier');
        $session->remove('code_validation');

        $this->addFlash('success', '✅ Commande ' . $commande->getNumero() . ' confirmée !');
        return $this->redirectToRoute('app_commande_succes', [
            'id' => $commande->getId()
        ]);
    }

    // ✅ Page de succès
    #[Route('/commande/succes/{id}', name: 'app_commande_succes')]
    public function succes(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        return $this->render('commande/succes.html.twig', [
            'commande' => $commande,
        ]);
    }

    // ✅ Historique des commandes
#[Route('/commandes', name: 'app_commandes_historique')]
public function historique(EntityManagerInterface $em): Response
{
    $commandes = $em->getRepository(Commande::class)->findBy(
        ['user' => $this->getUser()],
        ['createdAt' => 'DESC']
    );

    return $this->render('commande/historique.html.twig', [
        'commandes' => $commandes,
    ]);
}

// ✅ Détail d'une commande
#[Route('/commandes/{id}', name: 'app_commande_detail')]
public function detail(int $id, EntityManagerInterface $em): Response
{
    $commande = $em->getRepository(Commande::class)->find($id);

    if (!$commande || $commande->getUser() !== $this->getUser()) {
        throw $this->createNotFoundException('Commande introuvable');
    }

    $lignes = $em->getRepository(LigneCommande::class)->findBy([
        'commande' => $commande
    ]);

    return $this->render('commande/detail.html.twig', [
        'commande' => $commande,
        'lignes'   => $lignes,
    ]);
}
}