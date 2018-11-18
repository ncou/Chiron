<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

trait StrategyAwareTrait
{
    /**
     * @var StrategyInterface
     */
    protected $strategy;

    /**
     * {@inheritdoc}
     */
    public function getStrategy(): ?StrategyInterface
    {
        return $this->strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function setStrategy(StrategyInterface $strategy): StrategyAwareInterface
    {
        $this->strategy = $strategy;

        return $this;
    }
}
