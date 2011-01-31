<?php
class SPARQL extends WCC {

  private $response_data;

  public function __construct(){}

  public function getParsedResponse($xml) {
    unset($this->response_data);
    if ($sxml = simplexml_load_string($xml)) {
      $this->getHeadVariables($sxml);
      $this->getResults($sxml);
    }
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
      $this->setId($result_data);
      $this->response_data['results'][] = $result_data;
    }
  }

  /**
   * Set id key on data if possible
   * @param array $data Passed by reference
   * @return void
   */
  private function setId(&$data) {
    $from_key = false;
    if (isset($data['s']))
      $from_key = $data['s'];
    elseif (isset($data['page']))
      $from_key = $data['page'];
    if ($from_key)
      $data['id'] = substr($from_key, strrpos($from_key, '/') + 1);
  }
}