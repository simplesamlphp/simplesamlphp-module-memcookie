<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\memcookie\Controller;

use PHPUnit\Framework\TestCase;
//use SimpleSAML\Auth\Source;
//use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\HTTP\RunnableResponse;
//use SimpleSAML\Module\multiauth\Auth\Source\MultiAuth;
use SimpleSAML\Module\memcookie\Controller;
use SimpleSAML\Session;
//use SimpleSAML\XHTML\Template;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;

/**
 * Set of tests for the controllers in the "memcookie" module.
 *
 * @package SimpleSAML\Test
 */
class MemcookieTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected $config;

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

        $this->session = Session::getSessionFromRequest();
    }


    /**
     * Test that a valid requests results in a RunnableResponse
     * @return void
     */
    public function testMemcookie(): void
    {
        $request = Request::create(
            '/',
            'GET',
            []
        );

        $c = new Controller\Memcookie($this->config, $this->session);
        $response = $c->main($request);

        $this->assertInstanceOf(RunnableResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
    }
}
