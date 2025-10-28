<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\memcookie\Controller;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\HTTP\RunnableResponse;
use SimpleSAML\Module\memcookie\Controller;
use SimpleSAML\Session;
use SimpleSAML\Utils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Set of tests for the controllers in the "memcookie" module.
 *
 * @package SimpleSAML\Test
 */
final class MemcookieTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $authsources;

    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Utils\HTTP */
    protected Utils\HTTP $http_utils;

    /** @var \SimpleSAML\Configuration */
    protected Configuration $module_config;

    /** @var \SimpleSAML\Session */
    protected Session $session;


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
            'simplesaml',
        );

        $session = $this->createMock(Session::class);
        $session->method('getData')->willReturn(['default-sp' => []]);
        $this->session = $session;


        $this->authsources = Configuration::loadFromArray(
            [
                'default-sp' => ['saml:SP'],
            ],
            '[ARRAY]',
            'simplesaml',
        );
        Configuration::setPreLoadedConfig($this->authsources, 'authsources.php', 'simplesaml');

        $this->http_utils = new class () extends Utils\HTTP {
            /** @param array<mixed> $params */
            public function setCookie(string $name, ?string $value, ?array $params = null, bool $throw = true): void
            {
                // stub
            }


            /** @param array<mixed> $parameters */
            public function redirectTrustedURL(string $url, array $parameters = []): void
            {
                // stub
            }
        };

        $this->module_config = Configuration::loadFromArray(
            [
                'authsource' => 'default-sp',
                'cookiename' => 'AuthMemCookie',
                'username' => 'uid',
                'groups' => null,
                'memcache.host' => '127.0.0.1',
                'memcache.port' => 11211,
            ],
            '[ARRAY]',
            'simplesaml',
        );
        Configuration::setPreLoadedConfig($this->module_config, 'module_authmemcookie.php', 'simplesaml');
    }


    /**
     * Test that a valid requests results in a RunnableResponse
     * @return void
     */
    public function testMemcookie(): void
    {
        $sysUtils = new Utils\System();
        if ($sysUtils->getOS() === $sysUtils::WINDOWS) {
            $this->markTestSkipped(
                'This test can only run on Linux because of the availability of the memcached-extension.',
            );
        }

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/module.php/memcookie/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $request = Request::create(
            '/module.php/memcookie/',
            'GET',
            [],
        );

        $c = new Controller\Memcookie($this->config, $this->session);
        $c->setHttpUtils($this->http_utils);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            /** @param array<mixed> $params */
            public function requireAuth(array $params = []): void
            {
                // stub
            }


            /** @return array<mixed> */
            public function getAttributes(): array
            {
                return ['uid' => ['dduck']];
            }
        });

        $response = $c->main($request);

        $this->assertInstanceOf(RunnableResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
