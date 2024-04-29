<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Repository\Storage;

use App\Entity\DoctrineEntity\Storage\Rack;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RackRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testRackTree()
    {
        $repository = $this->entityManager->getRepository(Rack::class);

        /** @var Rack[[Rack, int, string, string]] $tree */
        $tree = $repository->getTree();

        $this->assertCount(8, $tree);

        $this->assertSame(1, $tree[0][0]->getDepth());
        $this->assertCount(1, $tree[0][0]->getUlidTree());
        $this->assertCount(1, $tree[0][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[0][0]->getNameTree());
        $this->assertContains((string)$tree[0][0]->getUlid(), $tree[0][0]->getUlidTree());

        $this->assertSame(2, $tree[1][0]->getDepth());
        $this->assertCount(2, $tree[1][0]->getUlidTree());
        $this->assertCount(2, $tree[1][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[1][0]->getNameTree());
        $this->assertContains($tree[1][0]->getName(), $tree[1][0]->getNameTree());
        $this->assertContains((string)$tree[0][0]->getUlid(), $tree[1][0]->getUlidTree());
        $this->assertContains((string)$tree[1][0]->getUlid(), $tree[1][0]->getUlidTree());

        $this->assertSame(3, $tree[2][0]->getDepth());
        $this->assertCount(3, $tree[2][0]->getUlidTree());
        $this->assertCount(3, $tree[2][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains($tree[1][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains($tree[2][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains((string)$tree[0][0]->getUlid(), $tree[2][0]->getUlidTree());
        $this->assertContains((string)$tree[1][0]->getUlid(), $tree[2][0]->getUlidTree());
        $this->assertContains((string)$tree[2][0]->getUlid(), $tree[2][0]->getUlidTree());
    }

    public function testRackTreeWithExclusion()
    {
        $repository = $this->entityManager->getRepository(Rack::class);
        $otherRack = $repository->findOneBy(["name" => "Rack 1.1.2"]);

        /** @var Rack[] $tree */
        $tree = $repository->getTree($otherRack);
        $this->assertCount(7, $tree);
    }

    public function testPathName()
    {
        $repository = $this->entityManager->getRepository(Rack::class);
        $rack = $repository->findOneBy(["name" => "Rack 1.1.1"]);

        $this->assertSame("Rack 1 | Rack 1.1 | Rack 1.1.1", $rack->getPathName());
    }

    public function testNormalBoxRetrieval()
    {
        $repository = $this->entityManager->getRepository(Rack::class);
        /** @var Rack[] $racksWithBoxes */
        $racksWithoutBoxes = $repository->findAll();

        // Should still be 8 entries
        $this->assertCount(8, $racksWithoutBoxes);

        // Lets make sure the collection is not loaded
        $this->assertFalse($racksWithoutBoxes[0]->getBoxes()->isInitialized());
    }

    public function testPreloadedBoxRetrieval()
    {
        $repository = $this->entityManager->getRepository(Rack::class);
        /** @var Rack[] $racksWithBoxes */
        $racksWithBoxes = $repository->findAllWithBoxes();

        // Should still be 8 entries
        $this->assertCount(8, $racksWithBoxes);

        // Lets make sure we've loaded the collection already
        $this->assertTrue($racksWithBoxes[0]->getBoxes()->isInitialized());
    }
}
