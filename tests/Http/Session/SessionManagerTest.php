<?php

declare(strict_types=1);

namespace Chiron\Tests\Http\Middleware;

use Chiron\Http\Session\Session;
use Chiron\Http\Session\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    // the session object manager
    protected $manager;

    protected function setUp()
    {
        session_save_path('');
        $this->manager = $this->newSessionManager();
    }

    protected function newSessionManager(array $cookies = [])
    {
        return new SessionManager($cookies);
    }

    protected function teardown()
    {
        session_unset();
        if (session_id() !== '') {
            session_destroy();
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testStart()
    {
        $this->manager->start();
        $this->assertTrue($this->manager->isStarted());
    }

    /**
     * @runInSeparateProcess
     */
    public function testClear()
    {
        // get a test session and set some data
        $session = $this->manager->getSession();
        $session->set('foo', 'bar');
        $session->set('baz', 'dib');
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $_SESSION);
        // now clear it
        $this->manager->clear();
        $this->assertSame([], $_SESSION);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroy()
    {
        // get a test session and set some data
        $session = $this->manager->getSession();
        $session->set('foo', 'bar');
        $session->set('baz', 'dib');
        $this->assertTrue($this->manager->isStarted());
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $_SESSION);
        // now destroy it
        $this->manager->destroy();
        $this->assertFalse($this->manager->isStarted());
    }

    public function testSave()
    {
        $this->manager->save();
        $this->assertFalse($this->manager->isStarted());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSaveAndDestroy()
    {
        // get a test session and set some data
        $session = $this->manager->getSession();
        $session->set('foo', 'bar');
        $session->set('baz', 'dib');
        $this->assertTrue($this->manager->isStarted());
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $_SESSION);
        $this->manager->save();
        $this->manager->destroy();
        $session = $this->manager->getSession();
        $this->assertSame([], $_SESSION);
    }

    public function testGetSession()
    {
        $session = $this->manager->getSession();
        $this->assertInstanceof('\Chiron\Http\Session\Session', $session);
    }

    public function testisResumable()
    {
        // should not look active
        $this->assertFalse($this->manager->isResumable());
        // fake a cookie
        $cookies = [
            $this->manager->getName() => 'fake-cookie-value',
        ];
        $this->manager = $this->newSessionManager($cookies);
        // now it should look active
        $this->assertTrue($this->manager->isResumable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetAndRegenerateId()
    {
        $this->manager->start();
        $old_id = $this->manager->getId();
        $this->manager->regenerateId();
        $new_id = $this->manager->getId();
        $this->assertTrue($old_id != $new_id);
    }

    public function testSetAndGetName()
    {
        $expect = 'newName';
        $this->manager->setName($expect);
        $actual = $this->manager->getName();
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetSavePath()
    {
        $expect = '/new/save/path';
        $this->manager->setSavePath($expect);
        $actual = $this->manager->getSavePath();
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetCookieParams()
    {
        $expect = $this->manager->getCookieParams();
        $expect['lifetime'] = '999';
        $this->manager->setCookieParams($expect);
        $actual = $this->manager->getCookieParams();
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetCacheExpire()
    {
        $expect = 123;
        $this->manager->setCacheExpire($expect);
        $actual = $this->manager->getCacheExpire();
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetCacheLimiter()
    {
        $expect = 'private_no_cache';
        $this->manager->setCacheLimiter($expect);
        $actual = $this->manager->getCacheLimiter();
        $this->assertSame($expect, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testResume()
    {
        // should not look active
        $this->assertFalse($this->manager->isResumable());
        $this->assertFalse($this->manager->resume());
        // fake a cookie so a session looks available
        $cookies = [
            $this->manager->getName() => 'fake-cookie-value',
        ];
        $this->manager = $this->newSessionManager($cookies);
        $this->assertTrue($this->manager->resume());
        // now it should already active
        $this->assertTrue($this->manager->resume());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Session name provided contains invalid characters; must be alphanumeric only and cannot be empty
     */
    public function testSessionNameEmpty()
    {
        $this->manager->setName('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Session name cannot be a numeric
     */
    public function testSessionNameOnlyNumeric()
    {
        $this->manager->setName('123456');
    }
}
