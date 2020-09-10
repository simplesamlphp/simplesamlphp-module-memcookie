<?php

namespace SimpleSAML\Module\memcookie;

/**
 * This is a helper class for the Auth MemCookie module.
 * It handles the configuration, and implements the logout handler.
 *
 * @package SimpleSAMLphp
 */
class AuthMemCookie
{
    /**
     * @var AuthMemCookie|null This is the singleton instance of this class.
     */
    private static $instance = null;

    /**
     * @var \SimpleSAML\Configuration The configuration for Auth MemCookie.
     */
    private $config;


    /**
     * This function is used to retrieve the singleton instance of this class.
     *
     * @return \SimpleSAML\Module\memcookie\AuthMemCookie The singleton instance of this class.
     */
    public static function getInstance(): AuthMemCookie
    {
        if (self::$instance === null) {
            self::$instance = new AuthMemCookie();
        }

        return self::$instance;
    }


    /**
     * This function implements the constructor for this class. It loads the Auth MemCookie configuration.
     */
    private function __construct()
    {
        // load AuthMemCookie configuration
        $this->config = \SimpleSAML\Configuration::getConfig('authmemcookie.php');
    }


    /**
     * Retrieve the authentication source that should be used to authenticate the user.
     *
     * @return string The login type which should be used for Auth MemCookie.
     */
    public function getAuthSource(): string
    {
        return $this->config->getString('authsource');
    }


    /**
     * This function retrieves the name of the cookie from the configuration.
     *
     * @return string The name of the cookie.
     * @throws \Exception If the value of the 'cookiename' configuration option is invalid.
     */
    public function getCookieName(): string
    {
        $cookieName = $this->config->getString('cookiename', 'AuthMemCookie');
        if (!is_string($cookieName) || strlen($cookieName) === 0) {
            throw new \Exception(
                "Configuration option 'cookiename' contains an invalid value. This option should be a string."
            );
        }

        return $cookieName;
    }


    /**
     * This function retrieves the name of the attribute which contains the username from the configuration.
     *
     * @return string|null The name of the attribute which contains the username.
     */
    public function getUsernameAttr(): ?string
    {
        return $this->config->getString('username', null);
    }


    /**
     * This function retrieves the name of the attribute which contains the groups from the configuration.
     *
     * @return string|null The name of the attribute which contains the groups.
     */
    public function getGroupsAttr(): ?string
    {
        return $this->config->getString('groups', null);
    }


    /**
     * This function creates and initializes a Memcache object from our configuration.
     *
     * @return \Memcached A Memcache object initialized from our configuration.
     */
    public function getMemcache(): \Memcached
    {
        $memcacheHost = $this->config->getString('memcache.host', '127.0.0.1');
        $memcachePort = $this->config->getInteger('memcache.port', 11211);

        $class = class_exists('\Memcached') ? '\Memcached' : false;

        if (!$class) {
            throw new \Exception('Missing Memcached implementation. You must install either the Memcached extension.');
        }

        $memcache = new \Memcached();

        foreach (explode(',', $memcacheHost) as $memcacheHost) {
            $memcache->addServer($memcacheHost, $memcachePort);
        }

        return $memcache;
    }


    /**
     * This function logs the user out by deleting the session information from memcache.
     * @return void
     */
    private function doLogout(): void
    {
        $cookieName = $this->getCookieName();

        // check if we have a valid cookie
        if (!array_key_exists($cookieName, $_COOKIE)) {
            return;
        }

        $sessionID = $_COOKIE[$cookieName];

        // delete the session from memcache
        $memcache = $this->getMemcache();
        $memcache->delete($sessionID);

        // delete the session cookie
        \SimpleSAML\Utils\HTTP::setCookie($cookieName, null);
    }


    /**
     * This function implements the logout handler. It deletes the information from Memcache.
     * @return void
     */
    public static function logoutHandler(): void
    {
        self::getInstance()->doLogout();
    }
}
