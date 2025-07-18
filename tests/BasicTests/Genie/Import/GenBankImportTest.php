<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Genie\Import;

use App\Genie\Import\GenBankImport;
use PHPUnit\Framework\TestCase;

class GenBankImportTest extends TestCase
{
    public function testAddgenePlasmidImport(): void
    {
        $content = file_get_contents(__DIR__ . "/../../../fixtures/import/addgene_plasmid.gbk");
        $import = new GenBankImport($content);

        // Assertions
        $this->assertSame("Exported", $import->getLocusName());
        $this->assertSame("Double floxed Gq-coupled hM3D DREADD fused with mCherry under the control of human synapsin promoter.", $import->getDefinition());
        $this->assertSame("", $import->getAccession());
        $this->assertSame("", $import->getVersion());

        // Assert sequence
        $sequence = $import->getSequence();
        $this->assertEquals(7315, strlen($sequence));
        $this->assertStringStartsWith("CCTGCAGGCAGCTGCGCGCT", $sequence);
        $this->assertStringEndsWith("TTTTGCTGGCCTTTTGCTCACATGT", $sequence);

        // Assert features
        $features = $import->getFeatures();
        $this->assertCount(29, $features);

        $this->assertSame([7155, 7174], $features[28]["span"]);
        $this->assertFalse($features[28]["complement"]);
        $this->assertSame([786, 1496], $features[5]["span"]);
        $this->assertTrue($features[5]["complement"]);
    }

    public function testAddgenePlasmid2Import(): void
    {
        $content = file_get_contents(__DIR__ . "/../../../fixtures/import/addgene_plasmid_2.gbk");
        $import = new GenBankImport($content);

        // Assertions
        $this->assertSame("GFHX2KHFA8", $import->getLocusName());
    }

    public function testAddgenePlasmidImportJoin(): void
    {
        $content = file_get_contents(__DIR__ . "/../../../fixtures/import/addgene_plasmid_join.gbk");
        $import = new GenBankImport($content);

        $this->assertSame("X7YWLCSBMF", $import->getLocusName());


        // Assert features
        $features = $import->getFeatures();
        $this->assertCount(3, $features);

        $this->assertSame([2622, 3338], $features[1]["span"]);
        $this->assertFalse($features[1]["complement"]);

        $this->assertSame([6038, 6068], $features[2]["span"]);
        $this->assertTrue($features[2]["complement"]);
    }

    public function testBenchlingExport(): void
    {
        $content = file_get_contents(__DIR__ . "/../../../fixtures/import/benchling_export.gb");
        $import = new GenBankImport($content);

        // Assertions
        $this->assertSame("SUMO-CAII-SUMO-TdTevo", $import->getLocusName());
        $this->assertSame("", $import->getDefinition());
        $this->assertSame("", $import->getAccession());
        $this->assertSame("", $import->getVersion());

        // Assert sequence
        $sequence = $import->getSequence();
        $this->assertEquals(8271, strlen($sequence));
        $this->assertStringStartsWith("TTCTCATGTTTGACAGCTTATCATCGATAA", $sequence);
        $this->assertStringEndsWith("GCGTATCACGAGGCCCTTTCGTCTTCAAGAA", $sequence);

        // Assert features
        $features = $import->getFeatures();
        $this->assertCount(15, $features);

        $this->assertSame([313, 2955], $features[0]["span"]);
        $this->assertTrue($features[0]["complement"]);
        $this->assertSame("CDS", $features[0]["type"]);

        $this->assertSame([1870, 1879], $features[5]["span"]);
        $this->assertTrue($features[5]["complement"]);
        $this->assertSame("misc_feature", $features[5]["type"]);
    }
}
