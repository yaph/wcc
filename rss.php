<?php
class RSS {
  private $response_data;
  public function __construct(){}

  public function getParsedResponse($xml) {
    libxml_use_internal_errors(true);
    $sxml = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    if (empty($errors)) {
      $this->getItems($sxml);
      //$this->getChannel($sxml);
      return $this->response_data;
    }
    return false;
  }

  private function getChannel($sxml) {
    $channel = $sxml->channel;
//    foreach ($channel as $k => $v) {
//      $this->response_data['channel'][$k] = $v;
//    }
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
}