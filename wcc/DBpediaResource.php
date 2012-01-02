<?php
/**
 * A class for parsing DBpediaresources.
 */
class DBpediaResource extends WCC {

  private $dom;

  private $xmlns = array(
    'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#'
  );

  /**
   * An associative array of ISO 2 letter language codes
   * @var array
   */
  private $languages = array();

  public function __construct(){
    $this->dom = new DOMDocument();
  }

  public function getIndex($rdf) {
    $doc = $this->dom->loadXML($rdf);
    $rdfdescs = $this->dom->getElementsByTagNameNs($this->xmlns['rdf'], 'Description');
    foreach ($rdfdescs as $rdfdesc) {
      $about = $rdfdesc->getAttributeNs($this->xmlns['rdf'], 'about');
      if ($rdfdesc->hasChildNodes()) {
        foreach ($rdfdesc->childNodes as $node) {
          $name = $node->localName;
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
            $lang = $node->attributes->item(0)->value;
            $index[$about][$name][$lang][] = $node->nodeValue;
            // add to language array
            if (!isset($this->languages[$lang])) {
              $this->languages[$lang] = $lang;
            }
          }
        }
      }
    }
    return $index;
  }

  public function getFlatIndexByKeyAndLang(array $index, $key, $lang) {
    if (!is_array($index) || !isset($index[$key])) {
      return FALSE;
    }
    $flat = array();
    $subindex = $index[$key];
    foreach ($subindex as $idx => $value) {
      if (is_array($value)) {
        if (isset($value[$lang])) {
          $value = $value[$lang];
        }
        $flat[$idx] = $this->getFlatArray($value);
      }
    }
    return $flat;
  }

  public function getLanguages() {
    return $this->languages;
  }

  /**
   * Flatten array with one unique item to value of item.
   * @param array $array
   */
  private function getFlatArray(array $array) {
    $array = array_unique($array);
    $ret = NULL;
    if (1 == count($array)) {
      $ret = current($array);
    }
    else {
      $ret = $array;
    }
    return $ret;
  }
}