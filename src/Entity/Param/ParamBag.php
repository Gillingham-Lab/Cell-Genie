<?php
declare(strict_types=1);

namespace App\Entity\Param;

use Doctrine\Common\Collections\ArrayCollection;

class ParamBag
{
    private ArrayCollection $params;

    public function __construct()
    {
        $this->params = new ArrayCollection();
    }

    public function getParam(string $name): ?Param
    {
        if ($this->params->containsKey($name)) {
            return $this->params->get($name);
        } else {
            return null;
        }
    }

    public function setParam(string $name, Param $param)
    {
        $this->params->set($name, $param);
    }
}