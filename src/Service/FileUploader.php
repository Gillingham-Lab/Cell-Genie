<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\File;
use App\Form\DocumentationType;
use App\Form\VisualisationType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private User $user;

    public function __construct(
        private Security $security,
    ) {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->user = $user;
        }
    }

    public function upload(FormInterface $form): void
    {
        /** @var FormInterface $child */
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

            if (method_exists($object, "setVisualisation") && ($visualisation->getTitle() === null or $visualisation->getFileBlob() === null)) {
                $object->setVisualisation(null);
            }
        }
    }
}