<?php
/**
 * A class for parsing DBpediaresources.
 */
class DBpediaResource {

  const RDF_RESOURCE = 'http://dbpedia.org/resource/';

  const DEFAULT_LANG = 'en';

  /**
   * Namespace array
   * @var array
   */
  private $xmlns = array(
    'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#'
  );

  /**
   * DOMDocument instance
   * @var DOMDocument
   */
  private $dom;

  /**
   * Language
   * @var string
   */
  private $lang = '';

  /**
  * Resource URI
  * @var string
  */
  private $uri = '';

  /**
   * An associative array of ISO 2 letter language codes
   * @var array
   */
  private $languages = array();

  public function __construct(){
    $this->dom = new DOMDocument();
  }

  /**
   * Get an associative array of RDF objects keyed by predicates.
   * @param string $rdf
   * @param string $id
   * @param string $lang
   */
  public function getIndex($rdf, $id, $lang = self::DEFAULT_LANG) {
    $this->lang = $lang;
    $this->uri = self::RDF_RESOURCE . urlencode($id);
    $doc = $this->dom->loadXML($rdf);
    $rdfdescs = $this->dom->getElementsByTagNameNs($this->xmlns['rdf'], 'Description');
    foreach ($rdfdescs as $rdfdesc) {
      $about = $rdfdesc->getAttributeNs($this->xmlns['rdf'], 'about');
      if ($rdfdesc->hasChildNodes()) {
        foreach ($rdfdesc->childNodes as $node) {
          $name = $node->nodeName;
          // for empty nodes use node name as index key and 1st attribute value
          // as index value
          if (!$node->nodeValue) {
            if ($node->hasAttributes()) {
              $index[$about][$name][] = $node->attributes->item(0)->value;
            }
          }
          // for non empty nodes use node name as index key, value of 1st 
          // attribute (i.e. lang) as subkey and node value as index value
          elseif ($node->hasAttributes()) {
            $attr = $node->attributes->item(0);
            if ('lang' == $attr->name) {
              $lang = $attr->value;
              $index[$about][$name][$lang][] = $node->nodeValue;
              // add to language array
              if (!isset($this->languages[$lang])) {
                $this->languages[$lang] = $lang;
              }
            }
            // likely a data type that is omitted for the time being
            else {
              $index[$about][$name][] = $node->nodeValue;
            }
          }
        }
      }
    }
    return $index;
  }

  /**
  * Return array for $resource_uri with language nodes reduced to given or
  * default language
  * @param array $index
  */
  public function getDataFromIndex(array $index) {
    if (!is_array($index) || !isset($index[$this->uri])) {
      return FALSE;
    }
    $data = array();
    $subindex = $index[$this->uri];
    foreach ($subindex as $idx => $value) {
      if (is_array($value)) {
        if (isset($value[$this->lang])) {
          $data[$idx] = array_unique($value[$this->lang]);
        }
        // In some cases i.e. fullname the only language that is set is en
        elseif (isset($value[self::DEFAULT_LANG])) {
          $data[$idx] = array_unique($value[self::DEFAULT_LANG]);
        }
        elseif (in_array($idx, $data)) {
          $data[$idx] = array_unique($value);
        }
        else {
          $data[$idx] = $value;
        }
      }
    }
    return $data;
  }

  public function getLanguages() {
    return $this->languages;
  }
}