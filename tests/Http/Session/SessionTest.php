<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Session\Session;
use Chiron\Http\Session\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    protected $manager;

    protected $session;

    protected function setUp()
    {
        session_save_path('');
        $this->manager = $this->newSessionManager();
        $this->session = $this->manager->getSession();
    }

    protected function newSessionManager(array $cookies = [])
    {
        return new SessionManager(
            $cookies
        );
    }

    protected function getValue($key = null)
    {
        if ($key) {
            return $_SESSION[$key];
        } else {
            return $_SESSION;
        }
    }

    protected function setValue($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    /**
     * @runInSeparateProcess
     */
    public function testMagicMethods()
    {
        $this->assertNull($this->session->get('foo'));
        $this->session->set('foo', 'bar');
        $this->assertSame('bar', $this->session->get('foo'));
        $this->assertSame('bar', $this->getValue('foo'));
        $this->setValue('foo', 'zim');
        $this->assertSame('zim', $this->session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    /*
    public function testClear()
    {
        $this->session->set('foo', 'bar');
        $this->session->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));
        // now clear the data
        $this->session->clear();
        $this->assertSame([], $this->getValue());
        $this->assertNull($this->session->get('foo'));
        $this->assertNull($this->session->get('baz'));
    }*/

    /**
     * @runInSeparateProcess
     */
    // TODO : implémenter des méthodes magique Arrayable pour la classe Session !!!!
    /*
    public function testGetSession()
    {
        $this->session->set('foo', 'bar');
        $this->session->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));
        // now get the data
        $this->assertSame(array('foo' => 'bar', 'baz' => 'dib'), $this->session->getSession());
    }*/
    public function testGetDoesNotStartSession()
    {
        $this->assertFalse($this->manager->isStarted());
        $foo = $this->session->get('foo');
        $this->assertNull($foo);
        $this->assertFalse($this->manager->isStarted());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetResumesSession()
    {
        // fake a cookie
        $cookies = [
            $this->manager->getName() => 'fake-cookie-value',
        ];
        $this->manager = $this->newSessionManager($cookies);
        // should be active now, even though not started
        $this->assertTrue($this->manager->isResumable());
        // reset the session to use the new session manager
        $this->session = $this->manager->getSession();
        // this should restart the session
        $foo = $this->session->get('foo');
        $this->assertTrue($this->manager->isStarted());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetStartsSessionAndCanReadAfter()
    {
        // no session yet
        $this->assertFalse($this->manager->isStarted());
        // set it
        $this->session->set('foo', 'bar');
        // session should have started
        $this->assertTrue($this->manager->isStarted());
        // get it from the session
        $foo = $this->session->get('foo');
        $this->assertSame('bar', $foo);
        // make sure it's actually in $_SESSION
        $this->assertSame($foo, $_SESSION['foo']);
    }

/*
    public function testClearDoesNotStartSession()
    {
        $this->assertFalse($this->manager->isStarted());
        $this->session->clear();
        $this->assertFalse($this->manager->isStarted());
    }
*/

    /**
     * @runInSeparateProcess
     */
    public function testDeleteKey()
    {
        $this->session->set('foo', 'bar');
        $this->session->set('baz', 'dib');
        $this->assertSame('bar', $this->getValue('foo'));
        $this->assertSame('dib', $this->getValue('baz'));
        // now remove the key
        $this->session->remove('foo');
        $this->assertNull($this->session->get('foo'));
        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertSame('dib', $this->getValue('baz'));
    }
}
