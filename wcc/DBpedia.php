<?php
class DBpedia {

  const URI_RESOURCE = 'http://dbpedia.org/resource/';

  const URI_DATA = 'http://dbpedia.org/data/';

  const URI_REDIRECT = 'http://dbpedia.org/property/redirect';
  
  const URI_MEDIA_S = 'http://upload.wikimedia.org/wikipedia/commons/';
  
  const URI_MEDIA_R = 'http://upload.wikimedia.org/wikipedia/en/';

  private $_properties = array();

  private $_parentUri = '';
  
  private $_SPARQLResults = array();

  /**
   * Parse a SPARQL query response and set results property.
   * @param string $data
   * @param string $format
   */
  public function parseSPARQLResponse($data, $format = 'json') {
    switch ($format) {
      case 'json':
        $data = json_decode($data);
        $vars = $data->head->vars;
        $results = $data->results->bindings;
        foreach ($results as $r) {
          $values = array();
          foreach ($vars as $v) {
            $values[$v] = $r->$v->value;
          }
          $this->_SPARQLResults[] = $values;
        }
        break;
    }
  }

  public function getSPARQLResults() {
    return $this->_SPARQLResults;
  }

  /**
   * Decode JSON string to PHP object and start to process it.
   * @param string $JSON_string
   */
  public function parseJSON($JSON_string) {
    $JSON = json_decode($JSON_string);
    $this->_recurseJSON($JSON);
  }

  /**
   * Recurse through given JSON object.
   * @param object $JSON
   */
  private function _recurseJSON($JSON) {
    foreach ($JSON as $uri => $data) {
      if ($this->_isNamespace($uri)) {
        $this->_parentUri = $uri;
        $key = $uri;
      } else {
        $key = $this->_parentUri;
      }
      if ($this->_isIterable($data)) {
        $this->_recurseJSON($data);
        if (self::URI_REDIRECT != $uri
        && (false === strpos($uri, self::URI_RESOURCE))) {
          $this->_properties[$key] = $data;
        }
      }
    }
  }

  /**
   * Check wheter given data qualifies as a potential namespace URI.
   * @param $data
   * @return bool
   */
  private function _isNamespace($data) {
    if ('string' ==gettype($data) && 0 === strpos($data, 'http://')) {
      return true;
    }
    return false;
  }

  /**
   * Check wheter data can be iterated.
   * @param mixed $d
   * @return bool
   */
  private function _isIterable($d) {
    $t = gettype($d);
    return ('array' == $t || 'object' == $t) ? true : false;
  }
  
  public static function mediaUri($uri, $type = 'image') {
    return str_replace(self::URI_MEDIA_S, self::URI_MEDIA_R, $uri);
  }
  
  /**
   * Get the corrsponding data URI for a resource.
   * @param string $uri
   * @param string $format
   */
  public static function dataUri($uri, $format = 'json') {
    $uri = self::URI_DATA . self::idFromUri($uri);
    if ($format) {
      $uri .= '.' . $format;
    }
    return $uri;
  }
  
  public static function nameFromUri($uri) {
    return str_replace('_', ' ', self::idFromUri($uri));
  }
  
  public static function idFromUri($uri) {
    if (0 !== strpos($uri, 'http'))
      return $uri;
    return ltrim(strrchr(parse_url($uri, PHP_URL_PATH), '/'), '/');
  }

  /**
   * Get an array of properties keyed by property name.
   * @param string $name
   * Options:
   *  uri = complete URI
   *  property = property part of URI, i.e. the part after the last slash
   * @param string $lang
   */
  public function getProperties($name = 'uri', $lang = '') {
    return $this->_properties;
  }

  /**
   * Get property identified by $uri. If $lang is given, return corresponding 
   * language string.
   *
   * @param string $uri
   * @param string $lang
   */
  public function getProperty($uri, $lang = '') {
    $properties = $this->getProperties();
    if (!isset($properties[$uri])) {
      return false;
    }
    $data = $properties[$uri];
    if ($this->_isIterable($data)) {
      foreach ($data as $prop) {
        if (isset($prop->type) && isset($prop->value)) {
          $type = $prop->type;
          $value = $prop->value;
          if ($lang && 'literal' == $prop->type 
            && isset($prop->lang) && $lang == $prop->lang) {
            return $value;
          }
          if ('uri' == $prop->type) {
             return $value;
          }
        }
      }
    }

    return $data;
  }

  # FIXME can be removed
  public static $ns = array(
    'http://www.w3.org/2002/07/owl#sameAs',
    'http://xmlns.com/foaf/0.1/primaryTopic',
    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
    'http://www.w3.org/2002/07/owl#sameAs',
    'http://www.w3.org/2000/01/rdf-schema#comment',
    'http://www.w3.org/2004/02/skos/core#subject',
    'http://xmlns.com/foaf/0.1/depiction',
    'http://www.w3.org/2000/01/rdf-schema#label',
    'http://xmlns.com/foaf/0.1/name',
    'http://dbpedia.org/ontology/releaseDate',
    'http://xmlns.com/foaf/0.1/page',
    'http://dbpedia.org/ontology/runtime',
    'http://dbpedia.org/ontology/starring',
    'http://dbpedia.org/ontology/Work/runtime',
    'http://dbpedia.org/ontology/musicComposer',
    'http://dbpedia.org/property/wikiPageUsesTemplate',
    'http://dbpedia.org/property/name',
    'http://dbpedia.org/ontology/subsequentWork',
    'http://dbpedia.org/property/country',
    'http://dbpedia.org/property/hasPhotoCollection',
    'http://dbpedia.org/ontology/thumbnail',
    'http://dbpedia.org/property/writer',
    'http://dbpedia.org/property/director',
    'http://dbpedia.org/property/producer',
    'http://dbpedia.org/property/starring',
    'http://dbpedia.org/property/language',
    'http://dbpedia.org/property/released',
    'http://dbpedia.org/ontology/abstract',
    'http://dbpedia.org/property/reference',
    'http://dbpedia.org/property/wordnet_type',
    'http://dbpedia.org/property/id',
    'http://dbpedia.org/property/music',
    'http://dbpedia.org/ontology/budget',
    'http://dbpedia.org/ontology/writer',
    'http://dbpedia.org/property/title',
    'http://dbpedia.org/ontology/director',
    'http://dbpedia.org/ontology/language',
    'http://dbpedia.org/ontology/distributor',
    'http://dbpedia.org/property/distributor',
    'http://dbpedia.org/property/runtime',
    'http://dbpedia.org/property/budget',
    'http://dbpedia.org/property/followedBy',
    'http://dbpedia.org/ontology/previousWork',
  );
}