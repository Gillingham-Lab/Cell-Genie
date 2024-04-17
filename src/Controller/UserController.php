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
use MongoDB\BSON\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends AbstractController
{
    #[Route("/user", name: "app_user")]
    #[Route("/user/{user}", name: "app_user")]
    public function main(
        Security $security,
        User $user = null,
    ): Response {
        if ($user === null) {
            /** @var User $user */
            $user = $security->getUser();
        }

        return $this->render("ucp/user.main.html.twig", [
            "user" => $user,
            "toolbox" => new Toolbox([
                new EditTool(
                    path: $this->generateUrl("app_user_edit_self"),
                    icon: "user",
                    iconStack: "edit",
                ),
                new Tool(
                    path: $this->generateUrl("app_user_settings"),
                    icon: "user",
                    iconStack: "settings",
                )
            ]),
        ]);
    }

    public function add(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted("new", "User");

        $newUser = new User();
        $newUser->setGroup($security->getUser()?->getGroup());

        return $this->edit($request, $security, $entityManager, $newUser);
    }

    #[Route("/user/add", name: "app_user_add", priority: 10)]
    #[Route("/user/edit", name: "app_user_edit_self", priority: 10)]
    #[Route("/user/{user}/edit", name: "app_user_edit")]
    public function edit(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        User $user = null,
    ): Response {
        $new = false;
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        if ($user === null) {
            if ($request->get("_route") === "app_user_edit") {
                $this->denyAccessUnlessGranted("edit", $user);

                /** @var User $user */
                $user = $security->getUser();
                $returnTo = $this->generateUrl("app_user", ["user" => $user->getId()]);
            } else {
                $this->denyAccessUnlessGranted("new", "User");

                // New user
                $new = true;
                $user = new User();
                $user->setGroup($security->getUser()->getGroup());
                $user->setIsActive(true);
                $returnTo = $this->generateUrl("app_homepage");
            }
        } else {
            $this->denyAccessUnlessGranted("edit", $user);
            $returnTo = $this->generateUrl("app_user", ["user" => $user->getId()]);
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
            } catch (\Exception $e) {
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
    ) {
        $data = $user->getSettings() ?? new ParamBag();
        $form = $this->createForm(UserSettingsType::class, $data);
        $form->handleRequest($request);

        $serializer = new \Dunglas\DoctrineJsonOdm\Serializer([new BackedEnumNormalizer(), new UidNormalizer(), new DateTimeNormalizer(), new ArrayDenormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);

        if ($form->isSubmitted() and $form->isValid()) {
            $user->setSettings(clone $data);
            $entityManager->flush();
            $this->addFlash("success", "Settings were saved.");
            return $this->redirectToRoute("app_user_settings");
        }

        return $this->render("ucp/settings.html.twig", [
            "user" => $user,
            "form" => $form,
        ]);
    }
}