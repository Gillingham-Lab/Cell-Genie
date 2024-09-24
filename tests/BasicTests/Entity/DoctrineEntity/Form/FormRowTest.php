<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Form;

use App\Entity\DoctrineEntity\Form\FormRow;
use App\Genie\Enums\FormRowTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormRowTest extends TestCase
{
    public function testLabelProperty(): void
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getLabel());
        $formRow->setLabel('Label');
        $this->assertSame('Label', $formRow->getLabel());
    }

    public function testFieldNameProperty(): void
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getFieldName());
        $formRow->setLabel("Label#13 Special Treaty.7");
        $this->assertSame("_Label13SpecialTreaty7", $formRow->getFieldName());
    }

    public function testHelpProperty(): void
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getHelp());
        $formRow->setHelp('Help');
        $this->assertSame('Help', $formRow->getHelp());
    }

    public function testTypeProperty(): void
    {
        $formRow = new FormRow();

        $this->assertNull($formRow->getType());

        $formRow->setType(FormRowTypeEnum::TextType);
        $this->assertSame(FormRowTypeEnum::TextType, $formRow->getType());
    }

    public function testTypePropertyThrowsExceptionIfClassDoesNotExist(): void
    {
        $formRow = new FormRow();

        $this->expectException(\TypeError::class);
        $formRow->setType('SpamAndEggs'); // @phpstan-ignore argument.type
    }

    public function testTypePropertyThrowExceptionIfClassDoesNotImplementInterface(): void
    {
        $formRow = new FormRow();

        $this->expectException(\TypeError::class);
        $formRow->setType(self::class); // @phpstan-ignore argument.type
    }
}
