<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\User\User;
use App\Service\EnumerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StimulusController extends AbstractController
{
    #[Route('/stimulus/EnumeratedWidget', name: 'stimulus_enumerated_widget', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function enumeratedWidget(
        EnumerationService $enumerationService,
        Request $request,
        #[CurrentUser]
        User $user,
    ): Response {
        $response = [

        ];
        $statusCode = Response::HTTP_OK;

        try {
            $nextNumber = $enumerationService->getNextNumber($user, $request->get("enumeration_type"));

            if (is_null($nextNumber)) {
                $response["errors"] = [
                    ["message" => "No new number generated. This might be because enumeration type is unknown."],
                ];
                $statusCode = Response::HTTP_BAD_REQUEST;
            } else {
                $response["next_number"] = $nextNumber;
            }
        } catch (\LogicException $exception) {
            $response["errors"] = [
                ["message" => $exception->getMessage()],
            ];
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        return new JsonResponse($response, $statusCode);
    }
}
