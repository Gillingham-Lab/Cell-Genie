<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GinPathFilter extends AbstractExtension
{
    function getFilters()
    {
        return [
            new TwigFilter("GinPathQRCode", function (string $s) {
                mt_srand(2022);
                $barcode = new \TCPDF2DBarcode("gin://$s", "QRCODE,L");
                $pngData = base64_encode($barcode->getBarcodePngData(5, 5));
                mt_srand(intval(microtime(true)));

                return "<img style='height: 100px' src='data:image/png;base64,$pngData'>";
            }, ["is_safe" => ["html"]]),
        ];
    }
}