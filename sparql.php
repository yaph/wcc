<?php
class SPARQL {
  private $response_data;
  public function __construct(){}

  public function getParsedResponse($xml) {
    $sxml = simplexml_load_string($xml);
    $this->getHeadVariables($sxml);
    $this->getResults($sxml);
    return $this->response_data;
  }

  private function getHeadVariables($sxml) {
    $vars = $sxml->head->variable;
    foreach ($vars as $v) {
      $this->response_data['labels'][] = $this->getAttrVal($v->attributes(), 'name');
    }
  }

  private function getResults($sxml) {
    $results = $sxml->results->result;
    foreach ($results as $r) {
      $binding = $r->binding;
      $result_data = array();
      foreach ($binding as $b) {
        $val = '';
        if (isset($b->literal))
          $val = (string) $b->literal;
        elseif (isset($b->uri))
          $val = (string) $b->uri;
        $name = $this->getAttrVal($b->attributes(), 'name');
        $result_data[$name] = $val;
      }
      $this->response_data['results'][] = $result_data;
    }
  }

  private function getAttrVal($attr, $name) {
    foreach ($attr as $k => $v) {
      if ($name == $k) {
        return (string) $v;
      }
    }
    return false;
  }
}