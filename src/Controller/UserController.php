<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Param\ParamBag;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Form\User\UserSettingsType;
use App\Form\User\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route("/user/{user}", name: "app_user")]
    #[IsGranted(new Expression("object ? is_granted('view', object) : is_granted('ROLE_USER')"), "user")]
    public function main(
        #[CurrentUser]
        User $currentUser,
        ?User $user = null,
    ): Response {
        return $this->render("ucp/user.main.html.twig", [
            "user" => $user ?? $currentUser,
            "toolbox" => new Toolbox([
                new EditTool(
                    path: $user ? $this->generateUrl("app_user_edit", ["user" => $user->getId()]) : $this->generateUrl("app_user_edit"),
                    icon: "user",
                    enabled: $this->isGranted("edit", $user ?? $currentUser),
                    iconStack: "edit",
                ),
                new Tool(
                    path: $this->generateUrl("app_user_settings"),
                    icon: "user",
                    enabled: $this->isGranted("edit", $user ?? $currentUser),
                    iconStack: "settings",
                )
            ]),
        ]);
    }

    #[Route("/user/add", name: "app_user_add", priority: 10)]
    #[Route("/user/edit/{user}", name: "app_user_edit", priority: 10)]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[IsGranted(
        attribute: new Expression("object['request'].get('_route') === 'app_user_add' ? is_granted('new', 'User') : object['user'] ? is_granted('edit', object['user']) : is_fully_authenticated()"),
        subject: [
            "request" => new Expression("request"),
            "user",
        ]
    )]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        #[CurrentUser]
        User $currentUser,
        ?User $user = null,
    ): Response {
        $new = false;

        // Create empty user
        if ($request->get("_route") === "app_user_add") {
            // New user
            $new = true;
            $user = new User();
            $user->setGroup($currentUser->getGroup());
            $user->setIsActive(true);
            $returnTo = $this->generateUrl("app_homepage");
        } else {
            if ($user === null) {
                $user = $currentUser;
                $returnTo = $this->generateUrl("app_user");
            } else {
                $returnTo = $this->generateUrl("app_user", ["user" => $user->getId()]);
            }
        }

        $form = $this->createForm(UserType::class, $user, [
            "save_button" => true,
            "require_password" => $new,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                // Compose full name
                $user->setFullName("{$user->getFirstName()} {$user->getLastName()}");

                if ($user->getPlainPassword()) {
                    $encodedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                    $user->setPassword($encodedPassword);
                    $user->eraseCredentials();
                }

                $entityManager->persist($user);
                $entityManager->flush();

                // Success messages
                if ($new) {
                    $this->addFlash("success", "The user was created.");
                    return $this->redirectToRoute("app_homepage");
                } else {
                    $this->addFlash("success", "The user entry was changed.");
                    return $this->redirectToRoute("app_user", ["user" => $user->getId()]);
                }
            } catch (Exception $e) {
                $this->addFlash("error", "An error occured: {$e->getMessage()}");
            }
        }

        return $this->render("ucp/form.user.html.twig", [
            "user" => $user,
            "form" => $form,
            "returnTo" => $returnTo,
        ]);
    }

    #[Route("/user/settings/change", name: "app_user_settings", priority: 10)]
    public function settings(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser]
        User $user,
    ): Response {
        $data = $user->getSettings();
        $form = $this->createForm(UserSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $user->setSettings($user->getSettings()->mergeBag($form->getData()));

            $entityManager->flush();
            $this->addFlash("success", "Settings were saved.");
            return $this->redirectToRoute("app_user_settings");
        }

        return $this->render("ucp/settings.html.twig", [
            "icon" => "user",
            "iconStack" => "settings",
            "title" => "User settings",
            "user" => $user,
            "form" => $form,
        ]);
    }
}