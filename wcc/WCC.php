<?php
require dirname(__FILE__) . '/autoload.php';
/**
 * A web client class with built-in caching.
 */
class WCC {
  /**
   * Set to the full URL when the request method is called.
   * @var string
   */
  protected $url = '';

  /**
   * File system root path to cache dir.
   * @var string
   */
  protected $fs_cache_root_path = '/tmp';

  /**
   * Cache lifetime in seconds or false.
   * @var int|bool
   */
  protected $cache_lifetime = false;

  /**
   * Fallback to cache if it exists and live data can't be fetched.
   * @var bool
   */
  protected $cache_fallback = false;

  /**
   * Cache object
   * @var object
   */
  protected $cache = NULL;

  /**
   * Creates a WCC object
   * @param array $opts Associative options array
   * @return void
   */
  public function __construct(array $opts) {
    if (isset($opts['cache_lifetime']))
      $this->cache_lifetime = $opts['cache_lifetime'];
    if (isset($opts['fs_cache_root_path']))
      $this->fs_cache_root_path = $opts['fs_cache_root_path'];
    if (isset($opts['cache_fallback']))
      $this->cache_fallback = $opts['cache_fallback'];
    if(!(isset($opts['cache_compress']) && $level = (int) $opts['cache_compress']))
      $level = 0;
    $this->cache = new FSCache($this->fs_cache_root_path, $level);
    $this->http = new HTTP();
  }

  /**
   * Requests data from Web or tries to load from cache if $cache_lifetime is 
   * an integer value. If cache_fallback is set to true, date from cache will
   * be returned if it exists and live data could not be fetched.
   * 
   * @param string $url Full URL or base URL when params is set to add a query string
   * @param array $params Associative array of URL parameters and values
   * @param int | false $cache_lifetime Cache lifetime in seconds
   * @return mixed $response Data loaded from cache or Web
   */
  public function request($url, $params = array(), $cache_lifetime = false) {
    $get_from_cache = $response = $cache_exists = false;

    $this->url = $this->getRequestURL($url, $params);

    // determine wether to try to load data from cache
    if (false === $cache_lifetime)
      $cache_lifetime = $this->cache_lifetime;
    if (false !== $cache_lifetime)
      $get_from_cache = true;

    // try to load from cache
    if ($get_from_cache) {
      $id = $this->cache->getIDFromURL($this->url);
      $response = $this->cache->get($id, $cache_lifetime);
    }

    // load from Web
    if (!$get_from_cache || !$response) {
      $response = $this->http->get($this->url);
    }

    // try to fallback to cache if set and on response
    if ($this->cache_fallback && !$response ) {
      $response = $this->cache->get($id, 0);
    }

    // cache data if data exists and caching is requested
    if ($response && $get_from_cache) {
      if (!isset($id))
        $id = $this->cache->getIDFromURL($this->url);
      $this->cache->set($id, $response);
    }

    return $response;
  }

  public function getAttrVal($attr, $name) {
    foreach ($attr as $k => $v) {
      if ($name == $k) {
        return (string) $v;
      }
    }
    return false;
  }

  /**
   * Returns full URL requested
   * @param void
   * @return string $url
   */
  public function getURL() {
    return $this->url;
  }

  /**
   * Returns HTTP status code returned after Web request
   * @param void
   * @return int $status
   */
  public function getHTTPStatus() {
    return $this->http->getStatus();
  }

  /**
   * Returns full URL to request, params are added when set
   * @param string $url Full URL or base URL when params is set to add a query string
   * @param array $params Associative array of URL parameters and values
   * @return string $url
   */
  private function getRequestURL($url, $params = array()) {
    if ($params) {
      asort($params);
      $url .= '?' . http_build_query($params, '', '&');
    }
    return $url;
  }
}

/**
 * A class for performing HTTP requests
 */
class HTTP {
  /**
   * HTTP status code received after request
   * @var int $status
   */
  private $status;

  /**
   * Performs a get HTTP request and returns the content received or false
   * @param string $url
   * @return string $body
   */
  public function get($url) {
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $body = curl_exec($ch);
    $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (200 > $this->status || 300 <= $this->status)
      return false;
    return $body;
  }

  /**
   * Returns the HTTP status code from request
   * @param string $url
   * @return int $this->status
   */
  public function getStatus() {
    return $this->status;
  }
}

/**
 * URLCache interface definition
 */
interface URLCache {
  public function get($id, $lifetime);
  public function set($id, $data);
  public function getIDFromURL($url);
}

/**
 * File system cache class the implements the URLCache interface
 */
class FSCache implements URLCache {
  /**
   * UMASK setting for writing and change permissions operations
   * @var int
   */
  const UMASK = 0777;

  /**
   * Full path to file system cache dir
   * @var string
   */

  private $root_dir = '/tmp';

  /**
  * Compress cache data using ZLIB functions with compression level.
  * @var int
  */
  protected $cache_compress = 0;

  /**
   * Create cache object
   * @param string $root_dir Full file system path of cache dir
   * @return void
   */
  public function __construct($root_dir, $cache_compress) {
    $this->root_dir = $root_dir;
    $this->cache_compress = $cache_compress;
  }

  /**
   * Get cached data if exists otherwise returns false
   * @param string $id File name
   * @param int $lifetime Cache lifetime
   * @return mixed Cached data or false
   */
  public function get($id, $lifetime) {
    if (file_exists($id)) {
      if ((0 === $lifetime) || (filemtime($id) + $lifetime > time()) ) {
        if ($this->cache_compress) {
          return implode(gzfile($id));
        }
        return file_get_contents($id);
      }
    }
    return false;
  }

  /**
   * Write cache data to file system and create directories as needed
   * @param int   $id file name
   * @param mixed $data
   * @return bool $succes
   */
  public function set($id, $data) {
    $success = false;
    $old = umask(0);
    if (!file_exists($id)) {
      $dir = dirname($id);
      if (!file_exists($dir))
        mkdir($dir, self::UMASK, true);
      touch($id);
    }
    if ($this->cache_compress) {
      $gz = gzopen($id, 'w' . $this->cache_compress);
      gzwrite($gz, $data);
      gzclose($gz);
    }
    else{
      file_put_contents($id, $data, LOCK_EX);
    }
    $success = chmod($id, self::UMASK);
    umask($old);
    return $success;
  }

  /**
   * Returns the cache ID created from the URL, which is an absolute file name
   * with URL host name as main directory and URL path as subdirectories.
   * @param string $url
   * @return string $id
   */
  public function getIDFromURL($url) {
    $dir = $this->root_dir;
    $parts = parse_url($url);
    if (isset($parts['host'])) {
      $dir .= '/' . $parts['host'];
    }
    $urlid = md5($url);
    $id = $dir . '/' . substr($urlid, 0, 2) . '/' . $urlid;
    return $id;
  }
}