<?php

declare(strict_types=1);

namespace Chiron\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : il faudra utiliser la clés qui est stockée dans APP_KEY et surtout utiliser la fonction hex2bin pour décoder cette chaine de caractére et l'utiliser comme une clés de bytes. Il faudra donc vérifier que la clés de byte fait bien 32 bytes une fois décodée via hex2bin et surtout pour utiliser hex2bin il faut vérifier que la chaine est bien de type hexa et que la longueur est un multiple de 2 (cad "even") [il faudrait même vérifier si la taille === 64 chars car c'est l'équivalent de 32 bytes] car sinon on aura un warning dans la méthode hex2bin et elle retournera false au lien de décoder la chaine.
//=> https://stackoverflow.com/questions/41194159/how-to-catch-hex2bin-warning

final class SecurityConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'security';

    public const KEY_BYTES_SIZE = 32;
    public const KEY_HEXA_SIZE = self::KEY_BYTES_SIZE * 2;

    protected function getConfigSchema(): Schema
    {
        // TODO : ajouter une constante = 64 dans cette classe pour stocker la taille de la clés attendue.
        // Key should be an hexadecimal value (ctype_xdigit) with 64 chars (the hexa representation of a key on 32 bytes)
        // TODO : améliorer la vérification sur la longeur === 64 caractéres, il faudra surement utiliser un assert() avec un callable custom pour vérifier que strlen === 64
        return Expect::structure([
            'key' => Expect::xdigit()->min(64)->max(64)->default(env('APP_KEY')),
        ]);
    }

    public function getKey(): string
    {
        return $this->get('key');
    }

    public function getRawKey(): string
    {
        return hex2bin($this->getKey());
    }
}
