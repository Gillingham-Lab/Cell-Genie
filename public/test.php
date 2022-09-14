<?php
declare(strict_types=1);
ob_start();

require dirname(__DIR__).'/vendor/autoload.php';

//$barcode = new TCPDF2DBarcode("gin:///cells/view/no/B16", "QRCODE,H");

$barcode = new TCPDF2DBarcode("gin:0123456789", "QRCODE,H");
$pngData = base64_encode($barcode->getBarcodePngData(5, 5));


//$barcode = new TCPDF2DBarcode("gin:0123456789", "DATAMATRIX");
//$pngData = base64_encode($barcode->getBarcodePngData(5, 5));

ob_clean();

print("Image: <img src='data:image/png;base64,$pngData'>");