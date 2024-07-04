<?php
declare(strict_types=1);

namespace App\Genie\Import;

class GenBankImport
{
    /**
     * @var array{
     *     metadata: array{},
     *     features: array<int, array{
     *          type: string,
     *          span: array{0: int, 1: int},
     *          complement: bool,
     *          annotations: array<string, string>,
     *     }>,
     *     sequence: string,
     *  }
     */
    private array $data = [
        "metadata" => [],
        "features" => [],
        "sequence" => "",
    ];

    /**
     * @throws ImportError
     */
    public function __construct(
        private string $content
    ) {
        $this->parse(trim($content));
    }

    /**
     * @throws ImportError
     */
    private function parse(string $content)
    {
        if (str_starts_with($content, "LOCUS") === false or str_ends_with($content, "//") === false) {
            throw new ImportError("The file format does not seem to be a genebank file.");
        }

        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        $lines = \explode("\n", $content);
        $data = [];

        $buffer = [];
        $bufferType = null;
        foreach ($lines as $line) {

            // Not having a space at the beginning of a line means it is a new main entry
            if (str_starts_with($line, " ") === false) {

                // If a buffer name has been set, we should submit the buffer to the corresponding sub parser
                if ($bufferType !== null) {
                    match ($bufferType) {
                        "LOCUS" => $this->parseLocus($buffer),
                        "DEFINITION" => $this->parseDefinition($buffer),
                        "ACCESSION" => $this->parseAccession($buffer),
                        "VERSION" => $this->parseVersion($buffer),
                        "FEATURES" => $this->parseFeatures($buffer),
                        "ORIGIN" => $this->parseOrigin($buffer),
                        default => null,
                    };
                }

                // Reset buffer
                $buffer = [$line];
                $bufferType = \preg_split("#\s{2,}#", $line)[0];
            } else {
                $buffer[] = $line;
            }
        }
    }

    private function parseLocus(array $buffer): void
    {
        $bufferLength = count($buffer);
        $firstLineParts = \preg_split("#\s{2,}#", $buffer[0], limit: 3);

        $this->data["metadata"]["locusName"] = $firstLineParts[1];
        $this->data["metadata"]["locusNameAppendix"] = $firstLineParts[2];

        for ($i = 1; $i < count($buffer); $i++) {
            $this->data["metadata"]["locusNameAppendix"] .= " " . trim($buffer[$i]);
        }
    }

    private function parseDefinition(array $buffer): void
    {
        $definition = [];
        foreach ($buffer as $bufferEntry) {
            $definition[] = trim(substr($bufferEntry, 10));
        }

        $definition = implode(" ", $definition);

        if (str_ends_with($definition, "..")) {
            $definition = substr($definition, 0, strlen($definition)-1);
        }

        if ($definition === ".") {
            $definition = "";
        }

        $this->data["metadata"]["definition"] = $definition;
    }

    private function parseAccession(array $buffer): void
    {
        $accession = \preg_split("#\s{2,}#", $buffer[0])[1];
        if (str_ends_with($accession, ".")) {
            $accession = substr($accession, 0, strlen($accession)-1);
        }

        $this->data["metadata"]["accession"] = $accession;
    }

    private function parseVersion(array $buffer): void
    {
        $version = \preg_split("#\s{2,}#", $buffer[0])[1];
        if (str_ends_with($version, ".")) {
            $version = substr($version, 0, strlen($version)-1);
        }

        $this->data["metadata"]["version"] = $version;
    }

    private function parseFeatures(array $buffer): void
    {
        // Assumption: File is nicely formatted with same column width for the feature table
        $columWidth = 0;
        $firstLineLength = strlen($buffer[0]);

        for ($i=strlen("FEATURES"); $i < $firstLineLength; $i++) {
            if ($buffer[0][$i] !== " ") {
                $columWidth = $i;
                break;
            }
        }

        $features = [];
        $currentAnnotation = null;
        $f = -1;

        foreach ($buffer as $line) {
            if (str_starts_with($line, "FEATURES")) {
                continue;
            }

            $firstPart = trim(substr($line, 0, $columWidth));
            $secondPart = trim(substr($line, $columWidth));

            if (strlen($firstPart) > 0) {
                // If the first part has more than spaces, a new feature begins
                $f++;
                $currentAnnotation = null;

                $features[$f] = [
                    "type" => $firstPart,
                ];

                if (str_starts_with($secondPart, "complement(")) {
                    $span = explode("..", substr($secondPart, strlen("complement("), -1));
                    $complement = true;
                } else {
                    $span = explode("..", $secondPart);
                    $complement = false;
                }

                $features[$f]["span"] = [
                    intval(str_starts_with("<", $span[0]) ? substr($span[0], 1) : $span[0]),
                    intval(str_ends_with(">", $span[1]) ? substr($span[1], 0, -1) : $span[1]),
                ];

                $features[$f]["complement"] = $complement;
                $features[$f]["annotations"] = [];
            } else {
                // If the first part is empty, the feature continues with annotations
                if (str_starts_with($secondPart, "/")) {
                    // If the string starts with a /, a new annotation begins
                    [$annotationName, $annotation] = explode("=", $secondPart, 2);

                    $currentAnnotation = substr($annotationName, 1);

                    $features[$f]["annotations"][$currentAnnotation] = trim($annotation);
                } else {
                    // If not, the annotation is continued. We then just blindly add to the annotation.
                    $features[$f]["annotations"][$currentAnnotation] .= " " . trim($secondPart);
                }

                // If the string starts and ends with a quote, we remove the quote.
                // This should only happen on the last line.
                if (str_starts_with($features[$f]["annotations"][$currentAnnotation], "\"") and str_ends_with($features[$f]["annotations"][$currentAnnotation], "\"")) {
                    $features[$f]["annotations"][$currentAnnotation] = substr($features[$f]["annotations"][$currentAnnotation], 1, -1);

                    // If the annotation additionally is a 'translation', we also remove spaces.
                    if ($currentAnnotation === "translation") {
                        $features[$f]["annotations"][$currentAnnotation] = str_replace(" ", "", $features[$f]["annotations"][$currentAnnotation]);
                    }
                }
            }
        }

        $this->data["features"] = $features;
    }

    private function parseOrigin(array $buffer): void
    {
        $sequence = "";

        foreach ($buffer as $bufferLine) {
            if (str_starts_with($bufferLine, "ORIGIN")) {
                continue;
            }

            $lineParts = explode(" ", trim($bufferLine));

            // Remove the first element - everything that is left should be sequence
            $number = array_shift($lineParts);

            $sequence .= implode("", $lineParts);
        }

        $this->data["sequence"] = strtoupper($sequence);
    }

    public function getLocusName(): string
    {
        return $this->data["metadata"]["locusName"] ?? "";
    }

    public function getDefinition(): string
    {
        return $this->data["metadata"]["definition"] ?? "";
    }

    public function getAccession(): string
    {
        return $this->data["metadata"]["accession"] ?? "";
    }

    public function getVersion(): string
    {
        return $this->data["metadata"]["version"] ?? "";
    }

    public function getFeatures(): array
    {
        return $this->data["features"];
    }

    public function getSequence(): string
    {
        return $this->data["sequence"];
    }
}