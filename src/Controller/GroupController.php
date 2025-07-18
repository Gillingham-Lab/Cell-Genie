<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\User\User;
use App\Form\User\UserGroupSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GroupController extends AbstractController
{
    #[Route("/group", "app_group")]
    #[IsGranted("ROLE_USER")]
    public function main(
        #[CurrentUser]
        User $currentUser,
    ): Response {
        $group = $currentUser->getGroup();

        return $this->render("ucp/group.main.html.twig", [
            "group" => $group,
        ]);
    }

    /**
     * @return array<string, mixed>|Response
     */
    #[Route("/group/settings", "app_group_settings")]
    #[IsGranted("ROLE_USER")]
    #[Template("ucp/settings.html.twig")]
    public function settings(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser]
        User $currentUser,
    ): array|Response {
        $group = $currentUser->getGroup();
        $data = $group->getSettings();

        $form = $this->createForm(UserGroupSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $group->setSettings($group->getSettings()->mergeBag($form->getData()));

            $entityManager->flush();
            $this->addFlash("success", "Settings were saved.");
            return $this->redirectToRoute("app_group_settings");
        }

        return [
            "icon" => "user.group",
            "iconStack" => "settings",
            "title" => "Group settings",
            "user" => $group,
            "form" => $form,
        ];
    }
}
