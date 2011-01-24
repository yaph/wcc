<?php
class RSS {
  private $response_data;
  public function __construct(){}

  public function getParsedResponse($xml) {
    $sxml = simplexml_load_string($xml);
//    $this->getChannel($sxml);
    $this->getItems($sxml);
    return $this->response_data;
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