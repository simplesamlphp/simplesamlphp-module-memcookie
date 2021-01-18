<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\memcookie\Controller;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\HTTP\RunnableResponse;
use SimpleSAML\Module\memcookie\Controller;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;

/**
 * Set of tests for the controllers in the "memcookie" module.
 *
 * @package SimpleSAML\Test
 */
class MemcookieTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected $authsources;

    /** @var \SimpleSAML\Configuration */
    protected $config;

    /** @var \SimpleSAML\Configuration */
    protected $module_config;

    /** @var \SimpleSAML\Session */
    protected $session;


    /**
     * Set up for each test.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Configuration::loadFromArray(
            [
                'module.enable' => ['memcookie' => true],
            ],
            '[ARRAY]',
            'simplesaml'
        );


        $this->authsources = Configuration::loadFromArray(
            [
                'default-sp' => ['saml:SP'],
            ],
            '[ARRAY]',
            'authsources.php'
        );
        Configuration::setPreLoadedConfig($this->authsources, 'authsources.php');

        $this->module_config = Configuration::loadFromArray(
            [
                'authsource' => 'default-sp',
                'cookiename' => 'AuthMemCookie',
                'username' => null,
                'groups' => null,
                'memcache.host' => '127.0.0.1',
                'memcache.port' => 11211,
            ],
            '[ARRAY]',
            'module_authmemcookie.php'
        );
        Configuration::setPreLoadedConfig($this->module_config, 'module_authmemcookie.php');

        $this->session = Session::getSessionFromRequest();
    }


    /**
     * Test that a valid requests results in a RunnableResponse
     * @return void
     */
    public function testMemcookie(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/module.php/memcookie/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $request = Request::create(
            '/module.php/memcookie/',
            'GET',
            []
        );

        $c = new Controller\Memcookie($this->config, $this->session);
        /** @var \SimpleSAML\HTTP\RunnableResponse $response */
        $response = $c->main($request);

        $this->assertInstanceOf(RunnableResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
