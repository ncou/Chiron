<?php
/**
 * Simple Session class helpers.
 */

namespace Chiron\Http\Session;

class Session
{
    // @var Chiron\Http\Session\SessionManager
    private $manager;

    /**
     * Constructor.
     *
     * @param SessionManager $manager The session manager.
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    public function get($key, $default = null)
    {
        $this->resumeSession();
        /*
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;*/
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->resumeOrStartSession();
        $_SESSION[$key] = $value;
    }

    // TODO : renommer cette méthode delete() en remove() ou en unset() ?
    public function remove($key)
    {
        if ($this->resumeSession()) {
            if (isset($_SESSION) && array_key_exists($key, $_SESSION)) {
                unset($_SESSION[$key]);
            }
        }
    }

    /*
        public function clear()
        {
            if ($this->resumeSession()) {
                $_SESSION = [];
            }
        }
    */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Resumes a previous session, or starts a new one.
     */
    private function resumeOrStartSession(): void
    {
        if (! $this->resumeSession()) {
            $this->manager->start();
        }
    }

    /**
     * If the session has already been started, or if a session is available, we try to resumes it.
     *
     * @return bool
     */
    private function resumeSession(): bool
    {
        if ($this->manager->isStarted() || $this->manager->resume()) {
            return true;
        }

        return false;
    }

    //********************************** TODO : code ci dessous à nettoyer !!!!!!!!!!
    /*
     * Returns true if the attribute exists.
     *
     * @param string $name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    /*
    public function has(string $name): bool
    {
        if (empty($_SESSION)) {
            return false;
        }

        return array_key_exists($name, $_SESSION);
    }*/

    /*
     * Sets multiple attributes at once: takes a keyed array and sets each key => value pair.
     *
     * @param array $values
     */
    /*
    public function replace(array $values): void
    {
        $_SESSION = array_replace_recursive($_SESSION, $values);
    }*/

    /*
     * Returns the number of attributes.
     *
     * @return int
     */
    /*
    public function count(): int
    {
        return count($_SESSION);
    }*/
}
