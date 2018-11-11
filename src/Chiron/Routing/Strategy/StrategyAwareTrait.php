<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

trait StrategyAwareTrait
{
    /**
     * @var \League\Route\Strategy\StrategyInterface
     */
    protected $strategy;
    /**
     * {@inheritdoc}
     */
    public function setStrategy(StrategyInterface $strategy) : StrategyAwareInterface
    {
        $this->strategy = $strategy;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getStrategy() : ?StrategyInterface
    {
        return $this->strategy;
    }
}
