<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Custom configuration file.
 * Contains configuration variables used through the application.
 */
class Titan extends BaseConfig
{

    /**
     * IP address of Kerridge host we want to connect to.
     * @var string
     */
    public $kerridge_host = "127.0.0.1";

    /**
     * Ports of Kerridge host we want to connect to.
     * @var array
     */
    public $kerridge_ports = "80,81,82";

    /**
     * How many requests each "user" can make to Titan per minute.
     * @var integer
     */
    public $global_rate_limit = 60;

    /**
     * How long we might hold a "user" "hostage" when they reach their rate limit.
     * @var integer
     */
    public $hostage_timer = 3;

    /**
     * Default cache duration in seconds.
     * If methods that utilize cache functionality are not able to find how long they should persist cache data, they resort to this value.
     * @var integer
     */
    public $default_cache_duration = 60;

    /**
     * JSON Web Token used when by Titan when communicating with Titan.
     * @var string
     */
    public $internal_json_web_token = "";

    /**
     * JSON Web Token key used to generate and validate JSON Web Tokens.
     * @var string
     */
    public $json_web_token_key = "";

}