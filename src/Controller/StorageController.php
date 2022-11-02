<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\BoxRepository;
use App\Repository\RackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StorageController extends AbstractController
{
    #[Route("/storage", name: "app_storage")]
    public function storageOverview(
        RackRepository $rackRepository,
        BoxRepository $boxRepository,
    ): Response {
        $racks = $rackRepository->findAll();

        return $this->render("parts/storage/storage.html.twig", [
            "racks" => $racks,
        ]);
    }
}