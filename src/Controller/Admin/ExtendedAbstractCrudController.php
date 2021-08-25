<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\File;
use App\Entity\User;
use App\Form\DocumentationType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class ExtendedAbstractCrudController extends AbstractCrudController
{
    public function processUploadedFiles(FormInterface $form): void
    {
        /** @var FormInterface $child */
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

            /** @var UploadedFile $uploadedFile */
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