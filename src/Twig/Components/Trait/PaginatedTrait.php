<?php
declare(strict_types=1);

namespace App\Twig\Components\Trait;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait PaginatedTrait
{
    #[LiveProp(writable: true, url: true)]
    #[Assert\Range(min: 0)]
    public int $page = 0;

    #[LiveProp(writable: true, url: true)]
    #[Assert\Range(min: 10, max: 100)]
    public int $limit = 30;

    public ?int $numberOfRows = null;

    /**
     * Goes to the next page
     * @return void
     */
    #[LiveAction]
    public function nextPage()
    {
        $this->page++;
    }

    /**
     * Goes to the previous page
     * @return void
     */
    #[LiveAction]
    public function previousPage()
    {
        if ($this->page > 0) {
            $this->page--;
        }
    }

    /**
     * Goes to the first page
     * @return void
     */
    #[LiveAction]
    public function firstPage()
    {
        $this->page = 0;
    }

    /**
     * Goes to the last page.
     * @return void
     * @throws Exception
     */
    #[LiveAction]
    public function lastPage()
    {
        $this->page = $this->getLastPageNumber() - 1;
    }

    /**
     * Returns the last page number (1-indexed)
     * @return int
     * @throws Exception
     */
    public function getLastPageNumber(): int
    {
        return intval(ceil($this->getNumberOfRows() / $this->limit));
    }

    /**
     * Returns the maximum number of rows.
     *
     * Must be implemented in the component.
     * @return int|null
     */
    abstract public function getNumberOfRows(): ?int;

    /**
     * Sets the number of rows.
     * @param int|null $numberOfRows
     * @return $this
     */
    private function setNumberOfRows(?int $numberOfRows): static
    {
        $this->numberOfRows = $numberOfRows;
        return $this;
    }
}