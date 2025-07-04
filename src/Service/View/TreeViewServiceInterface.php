<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\Toolbox\Toolbox;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @template T of object
 * @template-covariant T
 */
interface TreeViewServiceInterface
{
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
    );

    public function getNodeIcon(): ?string;

    /**
     * @param T $node
     * @return string
     */
    public function getNodeLabel(object $node): string;

    /**
     * @param T $node
     * @return string
     */
    public function getNodeUrl(object $node): string;

    /**
     * @param T $node
     * @return Toolbox
     */
    public function getNodeTools(object $node): ?Toolbox;

    /**
     * @param T $node
     * @return null|array{class-string, array<string, mixed>}|array{null, string}
     */
    public function getPostNodeComponent(object $node): ?array;

    /**
     * @param T $node
     * @return null|array{class-string, array<string, mixed>}|array{null, string}
     */
    public function getPreChildComponent(object $node): ?array;

    /**
     * @param T $node
     * @return null|array{class-string, array<string, mixed>}|array{null, string}
     */
    public function getPostChildComponent(object $node): ?array;
}