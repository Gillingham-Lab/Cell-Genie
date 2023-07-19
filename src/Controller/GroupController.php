<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\Substance\UserGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route("/group", "app_group")]
    public function main(
        Security $security,
        UserGroupRepository $groupRepository
    ): Response {
        $group = $security->getUser()->getGroup();

        return $this->render("ucp/group.main.html.twig", [
            "group" => $group,
        ]);
    }
}