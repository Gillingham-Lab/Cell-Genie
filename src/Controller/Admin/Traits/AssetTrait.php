<?php
declare(strict_types=1);

namespace App\Controller\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

trait AssetTrait
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
}
