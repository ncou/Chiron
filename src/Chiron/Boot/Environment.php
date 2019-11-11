<?php

declare(strict_types=1);

namespace Chiron\Boot;

// TODO : il faudrait surement une méthode pour renvoyer toutes les clés/valeurs du stype "toArray()" ou "all()"
// TODO : https://github.com/oscarotero/env/blob/master/src/Env.php
// TODO : il faut aussi gérer les cas ou il y a des double quottes au début/fin de la chaine.
// TODO : inspiration :   https://github.com/arrilot/dotenv-php/blob/master/src/DotEnv.php
//https://github.com/sebastiansulinski/dotenv/blob/master/src/DotEnv/DotEnv.php#L109

//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/Environment.php
//https://github.com/silverstripe/silverstripe-framework/blob/4/src/Core/EnvironmentLoader.php
//https://github.com/vlucas/phpdotenv/blob/master/src/Loader.php
//https://github.com/laravel/lumen-framework/blob/5.8/src/Bootstrap/LoadEnvironmentVariables.php#L52

// TODO : permettre de faire un getIterator sur cette classe, idem pour utiliser un ArrayAccess pour utiliser cette classe comme un tableau !!!!
// TODO : ajouter dans cette classe une méthode pour vérifier si on est en mode console (cad is_cli) + ajouter cela dans le fichier functions.php
class Environment implements EnvironmentInterface
{
    // TODO : il faudrait pas ajouter à cette liste "yes" et "no" ????
    private const VALUE_MAP = [
        'true'    => true,
        '(true)'  => true,
        'false'   => false,
        '(false)' => false,
        'null'    => null,
        '(null)'  => null,
        'empty'   => ''
    ];
    /** @var string|null */
    private $id = null;
    /** @var array */
    private $values = [];

    /**
     * @param array $values
     */
    // TODO : on devrait injecter le tableau de $values directement dans $_ENV en passant pas la méthode putEnv.
    // TODO : conditionner l'utilisation de putenv comme c'est fait ici : https://github.com/symfony/dotenv/blob/master/Dotenv.php#L139
    public function __construct(array $values = [])
    {
        $this->values = $values + $_ENV + $_SERVER;
        //$this->values = array_merge($_SERVER, $_ENV, $values);
    }
    /**
     * @inheritdoc
     */
    // TODO : voir si cette méthode est vraiment utile !!!
    public function hash(): string
    {
        if (empty($this->id)) {
            $this->id = md5(serialize($this->values));
        }

        return $this->id;
    }
    /**
     * @inheritdoc
     */
    public function set(string $name, $value)
    {
        $this->values[$name] = $_ENV[$name] = $value;
        putenv("$name=$value");
        $this->id = null;
    }
    /**
     * @inheritdoc
     */
    public function get(string $name, $default = null)
    {
        if (isset($this->values[$name])) {
            return $this->normalize($this->values[$name]);
        }

        return $default;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    // TODO : appeller la méthode normalize directement dans le constructeur au lieu de l'appeller dans le get. non ?????
    protected function normalize($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $alias = strtolower($value);
        if (isset(self::VALUE_MAP[$alias])) {
            return self::VALUE_MAP[$alias];
        }

        return $value;
    }


    /**
     * This handles the the global environment variables, it acts as getenv()
     * that handles the .env file in the root folder of a project.
     *
     * @param string            $key     The constant variable name
     * @param string|bool|mixed $default The default value if it is empty
     *
     * @return mixed The value based on requested variable
     */
    /*
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return;
        }

        //if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }*/
}
