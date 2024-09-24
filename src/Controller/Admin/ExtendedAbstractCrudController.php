<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\File;
use App\Form\AdminCrud\DocumentationType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class ExtendedAbstractCrudController extends AbstractCrudController
{
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead(<<< HTML
                <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            HTML)
            ->addHtmlContentToBody(<<< HTML
                <script>
                    let form_accordions = $("div.accordion-item");
                    let invalid_accordions = $(form_accordions.has(".is-invalid"))
                    
                    invalid_accordions.find(".accordion-header button.accordion-button i").first().after("<span class='fa fa-exclamation-circle fa-fw'></span>");
                    invalid_accordions.find(".accordion-header button.accordion-button").first().addClass("text-danger");
                
                </script>
            HTML);
    }

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