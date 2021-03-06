<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\FileRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    public function __construct(
        private FileRepository $fileRepository,
    ) {
    }

    #[Route("download/{fileid}", name: "file_download")]
    public function download(string $fileid): Response
    {
        try {
            $file = $this->fileRepository->find($fileid);
        } catch (ConversionException) {
            $file = null;
        }

        if (!$file) {
            throw new FileNotFoundException("The desired file was not found.");
        }

        $fileBlob = $file->getFileBlob();

        $content = stream_get_contents($fileBlob->getContent());

        return new Response(
            $content,
            status: 200,
            headers: [
                "Content-Type" => $file->getContentType(),
                "Content-Length" => $file->getContentSize(),
                "Content-Disposition" => 'attachment; filename="'. $file->getOriginalFileName() .'"',
            ],
        );
    }
}