<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    #[Route("/", name: "app_homepage")]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }
}
