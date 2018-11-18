<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

interface StrategyAwareInterface
{
    /**
     * Get the current strategy.
     *
     * @return null|StrategyInterface
     */
    public function getStrategy(): ?StrategyInterface;

    /**
     * Set the strategy implementation.
     *
     * @param StrategyInterface $strategy
     *
     * @return static
     */
    public function setStrategy(StrategyInterface $strategy): StrategyAwareInterface;
}
