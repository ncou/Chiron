<?php

declare(strict_types=1);

namespace Chiron\Routing\Traits;

use Chiron\Routing\Strategy\StrategyInterface;

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

    /**
     * {@inheritdoc}
     */
    public function strategy(StrategyInterface $strategy): StrategyAwareInterface
    {
        return $this->setStrategy($strategy);
    }
}
