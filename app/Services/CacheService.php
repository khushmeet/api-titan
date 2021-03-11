<?php

namespace App\Services;

use App\Exceptions\CacheException;

/**
 * This class provides a interface for CodeIgnitier4's Cache functionality.
 * @todo Write unit test.
 */
class CacheService
{

    /**
     * Becomes an instance of a cache handler.
     * @var class
     */
    private $cache;

    /**
     * Unique identifier.
     * @var string
     */
    private $key;

    /**
     * Cache save duration.
     * @var null|int
     */
    private $duration = null;

    /**
     * Unique identifier prefix.
     * Helps to "group" keys.
     * uri   = prefix $key with request->uri
     * ports = prefix $key with "ports"
     * @var string
     */
    private $prefix;

    /**
     * On __construct, set the prefix of the cache.
     * @param string   $prefix
     * @param null|int $duration How long we want something to be cached for.
     */
    public function __construct(string $prefix = "uri", int $duration = null)
    {
        $this->prefix = $prefix;
        $this->duration = $duration;
    }

    /**
     * Check if cache should be bypassed.
     * @return boolean
     */
    public function isBypassEnabled()
    {

        // get request
        $request = \Config\Services::request();

        // try to get the 'ignore_cache' query string from
        // the url if it exists then bypass is enabled
        if($request->getGet('ignore_cache') == "" && $request->getGet('ignore_cache') !== null){
            return true;
        }

        // no bypass!
        return false;

    }

    /**
     * Save data to cache.
     * @param mixed  $data     Data that is going to be cached.
     * @param string $u_key    Optional unique key value.
     * @param int    $duration How long cache should persist.
     */
    public function save($data, string $u_key = "", int $duration = null)
    {

        // run a quick "start" method
        $this->start($u_key);

        // if duration is not passed through
        if($duration === null){

            // attempt to use this instances duration
            if(isset($this->duration)){

                $duration = $this->duration;

            // else use default duration from titan
            }else{

                $titan = new \Config\Titan();
                $duration = $titan->default_cache_duration;

            }
        }

        // save the cache
        $this->cache->save($this->key, $data, $duration);

    }

    /**
     * Get data from cache.
     * @param string $u_key Optional unique key value.
     * @return various
     */
    public function get(string $u_key = "")
    {

        // run a quick "start" method
        $this->start($u_key);

        // attempt to get cache
        $data = $this->cache->get($this->key);

        // return the cache (or null if no cache found)
        return $data;

    }

    /**
     * Get meta data from cache.
     * @param string $u_key Optional unique key value.
     * @return array
     */
    public function getMetaData(string $u_key = "")
    {

        // run a quick "start" method
        $this->start($u_key);

        // attempt to get cache
        $data = $this->cache->getMetaData($this->key);

        // return the cache (or null if no cache found)
        return $data;

    }

    /**
     * Purge cached data.
     * @param boolean $purge_all If true, will purge all cached data.
     * @param string  $u_key     Optional unique key value.
     * @throws CacheException if cache was unable to purge data.
     */
    public function purge(bool $purge_all = true, string $u_key = "")
    {

        // run a quick "start" method
        $this->start($u_key);

        if($purge_all == true){

            // attempt to purge whole cache, returns true if successful
            $is_purged = $this->cache->clean();

            // if purge failed, create and throw CacheException
            if($is_purged == false){

                $message = "Cache was unable to be purged.";
                $exception = new CacheException($message);
                throw $exception;

            }

        }else{

            // attempts to purge key from cache, returns true if successful
            $is_purged = $this->cache->delete($this->key);

            // if purge failed, create and throw CacheException
            if($is_purged == false){

                $message = "Cache key (".$this->key.") was unable to be purged.";
                $exception = new CacheException($message);
                throw $exception;

            }

        }

    }

    /**
     * Creates necessary components for cache to work correctly.
     * @param string $u_key Optional unique key value.
     */
    private function start(string $u_key = "")
    {

        // sets $this->cache with instance of cache
        $this->cache = \Config\Services::cache();

        // if prefix is uri, use the uri as part of the key
        if($this->prefix == "uri"){

            // get request
            $request = \Config\Services::request();

            // get uri from $request, we can use this for the key
            $uri = (string)$request->uri;

            // urlencode uri, because the key cant contain "/" by the looks of it
            // also append $u_key to the end to allow
            $key = urlencode($uri).$u_key;

        }else{

            // prefix u_key with $this->prefix
            $key = $this->prefix.$u_key;

        }

        // sets $this->key
        $this->key = $key;

    }

}
