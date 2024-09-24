<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\FileRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class DownloadController extends AbstractController
{
    public function __construct(
        readonly private FileRepository $fileRepository,
    ) {
    }

    #[Route("download/{fileid}", name: "file_download")]
    public function download(string $fileid): Response
    {
        $file = $this->fileRepository->find($fileid);

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

    #[Route("/ressource/picture/{fileid}", name: "picture")]
    #[Cache(
        expires: "+3600 seconds",
        maxage: 3600,
        smaxage: 7200,
        public: true,
    )]
    public function picture(
        string $fileid
    ): Response {
        $file = $this->fileRepository->find($fileid);

        if (!$file) {
            throw new FileNotFoundException("The desired file was not found.");
        }

        $fileBlob = $file->getFileBlob();

        $content = stream_get_contents($fileBlob->getContent());

        $response = new Response(
            $content,
            status: 200,
            headers: [
                "Content-Type" => $file->getContentType(),
                "Content-Length" => $file->getContentSize(),
            ],
        );

        $response = $response->setCache([
            "last_modified" => $file->getUploadedOn(),
        ]);

        return $response;
    }
}