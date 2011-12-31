<?php
class RDF extends WCC {
  private $response_data;
  public function __construct(){}

  public function getParsedResponse($xml) {
    libxml_use_internal_errors(true);
    $sxml = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    if (empty($errors)) {
      $this->getItems($sxml);
      return $this->response_data;
    }
    return false;
  }

  private function getItems($sxml) {
    $items = $sxml->channel->item;
    foreach ($items as $i) {
      $item = array();
      foreach ($i as $k => $v) {
        if (!is_string($v))
          $v = (string) $v;
        $item[$k] = $v;
      }
      $this->response_data['items'][] = $item;
    }
  }

  public function getResource($rdf) {
    $reader = new XMLReader();
    $reader->xml($rdf);
    #    var_dump($rdf);
    while ($reader->read()) {
      $domnode = $reader->expand();
      if ($domnode->hasAttributes()) {
        foreach ($domnode->attributes as $attr)
        var_dump($attr->name, $attr->value);
      }
      var_dump($domnode->nodeName, $domnode->nodeValue);
    }
  }
}