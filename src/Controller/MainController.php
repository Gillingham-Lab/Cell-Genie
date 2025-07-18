<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MessageRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
    ) {}

    #[Route("/", name: "app_homepage")]
    #[Route("/page_{pageNr<\d+?0>")]
    public function homepage(int $pageNr = 1): Response
    {
        $query = $this->messageRepository->createQueryBuilder("m")
            ->orderBy("m.date", "DESC")
            ->getQuery();

        $pageSize = 5;

        $paginator = new Paginator($query);
        $totalMessages = count($paginator);
        $totalPages = ceil($totalMessages / $pageSize);

        $messages = $paginator->getQuery()->setFirstResult($pageSize * ($pageNr - 1))->setMaxResults($pageSize)->getResult();

        return $this->render('homepanel.html.twig', [
            "systemMessages" => [
                "messages" => $messages,
                "pageSize" => $pageSize,
                "totalPages" => $totalPages,
            ],
        ]);
    }
}
