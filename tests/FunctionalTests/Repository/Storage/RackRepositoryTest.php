<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Repository\Storage;

use App\Entity\DoctrineEntity\Storage\Rack;
use App\Repository\Storage\RackRepository;
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

    public function testRackTree(): void
    {
        /** @var RackRepository $repository */
        $repository = static::getContainer()->get(RackRepository::class);

        $tree = $repository->getTree();

        $this->assertCount(8, $tree);

        $this->assertSame(1, $tree[0][0]->getDepth());
        $this->assertCount(1, $tree[0][0]->getUlidTree());
        $this->assertCount(1, $tree[0][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[0][0]->getNameTree());
        $this->assertContains((string) $tree[0][0]->getUlid(), $tree[0][0]->getUlidTree());

        $this->assertSame(2, $tree[1][0]->getDepth());
        $this->assertCount(2, $tree[1][0]->getUlidTree());
        $this->assertCount(2, $tree[1][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[1][0]->getNameTree());
        $this->assertContains($tree[1][0]->getName(), $tree[1][0]->getNameTree());
        $this->assertContains((string) $tree[0][0]->getUlid(), $tree[1][0]->getUlidTree());
        $this->assertContains((string) $tree[1][0]->getUlid(), $tree[1][0]->getUlidTree());

        $this->assertSame(3, $tree[2][0]->getDepth());
        $this->assertCount(3, $tree[2][0]->getUlidTree());
        $this->assertCount(3, $tree[2][0]->getNameTree());
        $this->assertContains($tree[0][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains($tree[1][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains($tree[2][0]->getName(), $tree[2][0]->getNameTree());
        $this->assertContains((string) $tree[0][0]->getUlid(), $tree[2][0]->getUlidTree());
        $this->assertContains((string) $tree[1][0]->getUlid(), $tree[2][0]->getUlidTree());
        $this->assertContains((string) $tree[2][0]->getUlid(), $tree[2][0]->getUlidTree());
    }

    public function testRackTreeWithExclusion(): void
    {
        /** @var RackRepository $repository */
        $repository = static::getContainer()->get(RackRepository::class);

        $otherRack = $repository->findOneBy(["name" => "Rack 1.1.2"]);

        /** @var Rack[] $tree */
        $tree = $repository->getTree($otherRack);
        $this->assertCount(7, $tree);
    }

    public function testPathName(): void
    {
        /** @var RackRepository $repository */
        $repository = static::getContainer()->get(RackRepository::class);

        $rack = $repository->findOneBy(["name" => "Rack 1.1.1"]);

        $this->assertSame("Rack 1 | Rack 1.1 | Rack 1.1.1", $rack->getPathName());
    }

    public function testNormalBoxRetrieval(): void
    {
        /** @var RackRepository $repository */
        $repository = static::getContainer()->get(RackRepository::class);

        /** @var Rack[] $racksWithoutBoxes */
        $racksWithoutBoxes = $repository->findAll();

        // Should still be 8 entries
        $this->assertCount(8, $racksWithoutBoxes);

        // Let's make sure the collection is not loaded
        $this->assertInstanceOf(PersistentCollection::class, $racksWithoutBoxes[0]->getBoxes());
        $this->assertFalse($racksWithoutBoxes[0]->getBoxes()->isInitialized());
    }

    public function testPreloadedBoxRetrieval(): void
    {
        /** @var RackRepository $repository */
        $repository = static::getContainer()->get(RackRepository::class);

        /** @var Rack[] $racksWithBoxes */
        $racksWithBoxes = $repository->findAllWithBoxes();

        // Should still be 8 entries
        $this->assertCount(8, $racksWithBoxes);

        // Let's make sure we've loaded the collection already
        $this->assertInstanceOf(PersistentCollection::class, $racksWithBoxes[0]->getBoxes());
        $this->assertTrue($racksWithBoxes[0]->getBoxes()->isInitialized());
    }
}
