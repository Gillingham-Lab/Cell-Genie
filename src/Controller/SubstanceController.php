<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SubstanceController extends AbstractController
{
    #[Route("/substance/view/{substance}", "app_substance_view")]
    public function viewSubstance(Substance $substance) {
        return match($substance::class) {
            Antibody::class => $this->redirectToRoute("app_antibody_view", ["antibodyId" => $substance->getUlid()]),
            Chemical::class => $this->redirectToRoute("app_compound_view", ["compoundId" => $substance->getUlid()]),
            Protein::class => $this->redirectToRoute("app_protein_view", ["proteinId" => $substance->getUlid()]),
        };
    }
}