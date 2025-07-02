<?php
declare(strict_types=1);

namespace App\Controller\Admin\Traits;

use App\Entity\DoctrineEntity\File\File;
use App\Entity\DoctrineEntity\User\User;
use App\Form\AdminCrud\DocumentationType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileUploadTrait
{
    /**
     * @phpstan-ignore missingType.generics
     */
    public function processUploadedFiles(FormInterface $form): void
    {
        foreach ($form as $child) {
            $config = $child->getConfig();

            if (!$config->getType()->getInnerType() instanceof DocumentationType) {
                if ($config->getCompound()) {
                    $this->processUploadedFiles($child);
                }

                continue;
            }

            /** @var File $entity */
            $entity = $child->getData();

            /** @var ?UploadedFile $uploadedFile */
            $uploadedFile = $child->get("uploadedFile")->getData();

            # Check if a file has actually been uploaded.
            if ($uploadedFile) {
                $entity->setFromFile($uploadedFile);

                // Set uploader
                $uploader = $this->getUser();
                if ($uploader instanceof User) {
                    $entity->setUploadedBy($uploader);
                }
            }
        }

        parent::processUploadedFiles($form);
    }
}