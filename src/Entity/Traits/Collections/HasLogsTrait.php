<?php
declare(strict_types=1);

namespace App\Entity\Traits\Collections;

use App\Entity\DoctrineEntity\Log;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasLogsTrait
{
    #[ORM\ManyToMany(targetEntity: Log::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable]
    #[ORM\InverseJoinColumn(name: "log_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[ORM\OrderBy(["title" => "ASC"])]
    #[Assert\Valid]
    private Collection $logs;

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $vendorDocumentation): self
    {
        if (!$this->logs->contains($vendorDocumentation)) {
            $this->logs[] = $vendorDocumentation;
        }

        return $this;
    }

    public function removeLog(Log $vendorDocumentation): self
    {
        $this->logs->removeElement($vendorDocumentation);

        return $this;
    }
}