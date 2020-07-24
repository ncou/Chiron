<?php

declare(strict_types=1);

namespace Chiron\Config;

// TODO : éventuellement séparer le code et les interface en deux parties, une pour la partie "Modification" avec les méthodes reset/set/addConfig, et une partie "Injection" avec la méthode getConfigSectionName()
interface InjectableConfigInterface
{
    public function getConfigSectionName(): string;

    public function getSectionSubsetName(): ?string;
}
