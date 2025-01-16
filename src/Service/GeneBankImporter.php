<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\AnnotateableInterface;
use App\Entity\File;
use App\Entity\SequenceAnnotation;
use App\Genie\Import\GenBankImport;
use App\Genie\Import\ImportError;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;

class GeneBankImporter
{
    public function __construct(
        private LoggerInterface $logger,
    ) {

    }
    /**
     * @param Collection<int, File> $files
     * @throws ImportError
     */
    public function addSequenceAnnotations(
        AnnotateableInterface $annotateable,
        Collection $files,
        bool $importSequence = true,
        bool $importFeatures = true,
    ): bool {
        $import = false;

        /** @var File $file */
        foreach ($files as $file) {
            if ($file->isFreshlyUploaded() and (
                str_ends_with($file->getOriginalFileName(), ".gb") or
                str_ends_with($file->getOriginalFileName(), ".gbk")
                )
            ) {
                $this->logger->info("Imports file {$file->getOriginalFileName()}.");

                // Try to read the file
                $result = new GenBankImport($file->getFileBlob()->getContent());

                if ($importSequence) {
                    // Set the sequence from the import
                    $annotateable->setSequence($result->getSequence());

                    $this->logger->debug("imported sequence");
                }

                if ($importFeatures) {
                    // Set gene features
                    $features = $result->getFeatures();
                    $f = 0;

                    foreach ($features as $feature) {
                        if (!isset($feature["span"][0]) or !isset($feature["span"][1])) {
                            // Features without a span are skipped for now
                            continue;
                        }

                        $sequenceAnnotation = new SequenceAnnotation();
                        $sequenceAnnotation->setAnnotationType($feature["type"]);
                        $sequenceAnnotation->setAnnotationStart($feature["span"][0]);
                        $sequenceAnnotation->setAnnotationEnd($feature["span"][1]);
                        $sequenceAnnotation->setIsComplement($feature["complement"]);

                        $this->logger->debug("Imports a feature with type {$feature['type']}<{$feature['span'][0]},{$feature['span'][1]}> (complement: {$feature['complement']})");

                        // Now we try to predict the name.
                        $annotations = $feature["annotations"];

                        if (isset($annotations["label"])) {
                            $sequenceAnnotation->setAnnotationLabel($annotations["label"]);
                        } elseif (isset($annotations["gene"])) {
                            $sequenceAnnotation->setAnnotationLabel($annotations["gene"]);
                        } else {
                            $sequenceAnnotation->setAnnotationLabel("{$feature['type']}<{$feature['span'][0]},{$feature['span'][1]}>");
                        }

                        // Import color if given
                        if (isset($annotations["ApEinfo_fwdcolor"])) {
                            $sequenceAnnotation->setColor($annotations["ApEinfo_fwdcolor"]);
                        } elseif (isset($annotations["note"])) {
                            // SnapGene genbank (does not support multi-coloured features)
                            $colorMatches = [];
                            $doesMatch = preg_match("/color:\s+(#[0-9A-Fa-f]{6})/", $annotations["note"], $colorMatches);

                            if ($doesMatch) {
                                $sequenceAnnotation->setColor($colorMatches[1]);
                            }
                        }

                        $sequenceAnnotation->setAnnotations($annotations);

                        // Skip if one with the same label already exists
                        if ($annotateable->getSequenceAnnotations()->exists(fn(int $key, SequenceAnnotation $e) => $e->getAnnotationLabel() === $sequenceAnnotation->getAnnotationLabel()) === false) {
                            $annotateable->addSequenceAnnotation($sequenceAnnotation);

                            $f++;
                        }
                    }

                    $this->logger->debug("Imported {$f} features.");
                }

                $import = true;
            }
        }

        return $import;
    }
}