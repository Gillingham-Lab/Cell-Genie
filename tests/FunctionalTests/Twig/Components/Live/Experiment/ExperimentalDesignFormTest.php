<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\User\UserGroupRepository;
use App\Repository\User\UserRepository;
use App\Twig\Components\Live\Experiment\ExperimentalDesignForm;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

class ExperimentalDesignFormTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
    }

    public function testCanRender(): void
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
        );

        $testComponent->actingAs($this->getContainer()->get(UserRepository::class)->findOneByEmail("admin@example.com"));

        $this->assertStringContainsString("Save and return", $testComponent->render()->toString());
        $this->assertStringContainsString("Save", $testComponent->render()->toString());
    }

    public function testFormCanSubmit(): void
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
        );

        $testComponent->actingAs($this->getContainer()->get(UserRepository::class)->findOneByEmail("admin@example.com"));

        $response = $testComponent
            ->set("experimental_design", [
                "_general" => [
                    "number" => "Test",
                    "shortName" => "Short Name",
                    "longName" => "Long Name",
                    "ownership" => [
                        "owner" => $this->getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com")->getId()->toRfc4122(),
                        "group" => $this->getContainer()->get(UserGroupRepository::class)->findOneByShortName("Research Group")->getId()->toRfc4122(),
                        "privacyLevel" => PrivacyLevel::Group,
                    ],
                ],
                "_fields" => [
                    "fields" => [
                        [
                            "role" => ExperimentalFieldRole::Top->value,
                            "variableRole" => ExperimentalFieldVariableRoleEnum::Group->value,
                            "weight" => 0,
                            "exposed" => true,
                            "referenced" => false,
                            "referenceValue" => null,
                            "formRow" => [
                                "_type" => [
                                    "type" => FormRowTypeEnum::TextType->value,
                                    "label" => "Test",
                                    "help" => "",
                                ],
                            ],
                            "configuration" => [
                                "length_min" => null,
                                "length_max" => null,
                            ]
                        ]
                    ]
                ],
                "_models" => [
                    "models" => [

                    ]
                ]
            ])
            ->call("save")
            ->response()
        ;

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testFormCanSubmitAndReturn(): void
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
        );

        $testComponent->actingAs($this->getContainer()->get(UserRepository::class)->findOneByEmail("admin@example.com"));

        $response = $testComponent
            ->set("experimental_design", [
                "_general" => [
                    "number" => "Test",
                    "shortName" => "Short Name",
                    "longName" => "Long Name",
                    "ownership" => [
                        "owner" => $this->getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com")->getId()->toRfc4122(),
                        "group" => $this->getContainer()->get(UserGroupRepository::class)->findOneByShortName("Research Group")->getId()->toRfc4122(),
                        "privacyLevel" => PrivacyLevel::Group,
                    ],
                ],
                "_fields" => [
                    "fields" => [
                        [
                            "role" => ExperimentalFieldRole::Top->value,
                            "variableRole" => ExperimentalFieldVariableRoleEnum::Group->value,
                            "weight" => 0,
                            "formRow" => [
                                "_type" => [
                                    "type" => FormRowTypeEnum::TextType->value,
                                    "label" => "Test",
                                    "help" => "",
                                ],
                            ]
                        ]
                    ]
                ]
            ])
            ->call("submit")
            ->response()
        ;

        $this->assertSame(302, $response->getStatusCode());
    }

    public function testIncompleteFormGivesValidationErrors(): void
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
        );

        $testComponent->actingAs($this->getContainer()->get(UserRepository::class)->findOneByEmail("admin@example.com"));

        $this->expectException(UnprocessableEntityHttpException::class);
        $testComponent
            ->set("experimental_design", [
                "_general" => [
                    "number" => "EXP001",
                    "shortName" => "Short Name",
                    "longName" => "Long Name",
                    "ownership" => [
                        "owner" => $this->getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com")->getId()->toRfc4122(),
                        "group" => $this->getContainer()->get(UserGroupRepository::class)->findOneByShortName("Research Group")->getId()->toRfc4122(),
                        "privacyLevel" => PrivacyLevel::Group,
                    ],
                ],
                "_fields" => [
                    "fields" => [
                    ]
                ]
            ])
            ->call("submit")
        ;
    }

    public function testThatErrorDuringSavingGivesAnError(): void
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
        );

        $testComponent->actingAs($this->getContainer()->get(UserRepository::class)->findOneByEmail("admin@example.com"));

        try {
            $response = $testComponent
                ->set("experimental_design", [
                    "_general" => [
                        "number" => "EXP001",
                        "shortName" => "Experiment Design 1",
                        "longName" => "Long Name",
                        "ownership" => [
                            "owner" => $this->getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com")->getId()->toRfc4122(),
                            "group" => $this->getContainer()->get(UserGroupRepository::class)->findOneByShortName("Research Group")->getId()->toRfc4122(),
                            "privacyLevel" => PrivacyLevel::Group,
                        ],
                    ],
                    "_fields" => [
                        "fields" => [
                            [
                                "role" => ExperimentalFieldRole::Top->value,
                                "variableRole" => ExperimentalFieldVariableRoleEnum::Group->value,
                                "weight" => 0,
                                "formRow" => [
                                    "type" => FormRowTypeEnum::TextType->value,
                                    "label" => "Test",
                                    "help" => "",
                                ]
                            ]
                        ]
                    ]
                ])
                ->call("submit")
                ->response()
            ;
        } catch (Exception $e) {
            $this->assertInstanceOf(UnprocessableEntityHttpException::class, $e);
            $this->assertStringContainsString("Form validation failed", $e->getMessage());
        }
    }
}
