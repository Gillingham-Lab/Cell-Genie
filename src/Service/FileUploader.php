<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\File\File;
use App\Entity\DoctrineEntity\User\User;
use App\Form\DocumentationType;
use App\Form\VisualisationType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileUploader
{
    private ?User $user;

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->user = $user;
        } else {
            $this->user = null;
        }
    }

    /**
     * @param FormInterface<mixed> $form
     * @throws Exception If no user was retrieved from security service
     */
    public function upload(FormInterface $form): void
    {
        if (is_null($this->user)) {
            throw new Exception("Uploading a file is only possible if a user is logged in.");
        }

        foreach ($form as $child) {
            $config = $child->getConfig();

            if (!$config->getType()->getInnerType() instanceof DocumentationType and !$config->getType()->getInnerType() instanceof VisualisationType) {
                if ($config->getCompound()) {
                    $this->upload($child);
                }

                continue;
            }

            /** @var ?File $entity */
            $entity = $child->getData();

            /** @var ?UploadedFile $uploadedFile */
            $uploadedFile = $child->get("uploadedFile")->getData();

            // Check if a file has actually been uploaded.
            if ($uploadedFile and $entity) {
                $entity->setFromFile($uploadedFile);

                // Set uploader
                $uploader = $this->getUser();
                $entity->setUploadedBy($uploader);

                if ($entity->getTitle() === null) {
                    $entity->setTitle($uploadedFile->getClientOriginalName());
                }
            }
        }
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function updateFileSequence(object $object): void
    {
        if (method_exists($object, "getAttachments")) {
            $i = 0;
            /** @var File $attachment */
            foreach ($object->getAttachments() as $attachment) {
                $attachment->setOrderValue($i);
                $i++;
            }
        }

        if (method_exists($object, "getVisualisation")) {
            /** @var File $visualisation */
            $visualisation = $object->getVisualisation();

            if ($visualisation !== null && method_exists($object, "setVisualisation") && $visualisation->isMarkedForRemoval() === true) {
                $this->entityManager->remove($visualisation);
                $object->setVisualisation(null);
            }
        }
    }
}
