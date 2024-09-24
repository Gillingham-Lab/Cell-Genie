<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\User\UserGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GroupController extends AbstractController
{
    #[Route("/group", "app_group")]
    public function main(
        #[CurrentUser]
        $currentUser,
    ): Response {
        $group = $currentUser->getGroup();

        return $this->render("ucp/group.main.html.twig", [
            "group" => $group,
        ]);
    }
}