<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Form;

use App\Entity\DoctrineEntity\Form\FormRow;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormRowTest extends TestCase
{
    public function testLabelProperty()
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getLabel());
        $formRow->setLabel('Label');
        $this->assertSame('Label', $formRow->getLabel());
    }

    public function testHelpProperty()
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getHelp());
        $formRow->setHelp('Help');
        $this->assertSame('Help', $formRow->getHelp());
    }

    public function testTypeProperty()
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getType());

        $formRow->setType(TextType::class);
        $this->assertSame(TextType::class, $formRow->getType());
    }

    public function testTypePropertyThrowsExceptionIfClassDoesNotExist()
    {
        $formRow = new FormRow();

        $this->expectException(\InvalidArgumentException::class);
        $formRow->setType('SpamAndEggs');
    }

    public function testTypePropertyThrowExceptionIfClassDoesNotImplementInterface()
    {
        $formRow = new FormRow();

        $this->expectException(\InvalidArgumentException::class);
        $formRow->setType(self::class);
    }
}
