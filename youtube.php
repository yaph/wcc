<?php
class YouTube extends WCC {

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
    $items = $sxml->entry;
    foreach ($items as $i) {
      $item = array();
      foreach ($i as $k => $v) {
        switch($k) {
          case 'id':
            // example id "tag:youtube.com,2008:video:5HU5_LptFl0"
            $parts = explode(':', $v);
            $item[$k] = end($parts);
            break;
          case 'author':
            $item[$k] = array(
              'name' => (string) $v->name,
              'uri' => (string) $v->uri
            );
            break;
          case 'link':
            $item[$k][] = array(
              'rel' => $this->getAttrVal($v->attributes(), 'rel'),
              'href' => $this->getAttrVal($v->attributes(), 'href')
            );
            break;
          case 'content':
            $item[$k] = $this->getAttrVal($v->attributes(), 'src');
            break;
          case 'category':
            $item[$k][] = array(
              'scheme' => $this->getAttrVal($v->attributes(), 'scheme'),
              'term' => $this->getAttrVal($v->attributes(), 'term')
            );
            break;
          default:
            if (!is_string($v))
              $v = (string) $v;
            $item[$k] = $v;
        }
      }
      $this->response_data['items'][] = $item;
    }
  }
}