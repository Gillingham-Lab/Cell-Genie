<?php

namespace App\Controller;

use App\Repository\ExperimentTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperimentController extends AbstractController
{
    public function __construct(
        private ExperimentTypeRepository $experimentTypeRepository,
    ) {
    }

    #[Route('/experiment', name: 'app_experiments')]
    public function index(): Response
    {
        $experimentTypes = $this->experimentTypeRepository->findAll();

        return $this->render('parts/experiments/experiments.html.twig', [
            'controller_name' => 'ExperimentController',
            'experiment_types' => $experimentTypes,
        ]);
    }
}
