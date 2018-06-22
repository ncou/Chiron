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

    public function delete($key)
    {
        /*
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }*/
        if ($this->resumeSession()) {
            if (isset($_SESSION) && array_key_exists($key, $_SESSION)) {
                unset($_SESSION[$key]);
            }
        }
    }

    public function clear()
    {
        if ($this->resumeSession()) {
            $_SESSION = [];
        }
    }

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
        $this->delete($key);
    }

    /**
     * Resumes a previous session, or starts a new one, and loads the segment.
     */
    protected function resumeOrStartSession(): void
    {
        if (! $this->resumeSession()) {
            $this->manager->start();
        }
    }

    /**
     * Loads the segment only if the session has already been started, or if
     * a session is available (in which case it resumes the session first).
     *
     * @return bool
     */
    protected function resumeSession(): bool
    {
        if ($this->manager->isStarted() || $this->manager->resume()) {
            return true;
        }

        return false;
    }
}
