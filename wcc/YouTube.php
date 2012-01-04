<?php
class YouTube extends WCC {

  private $response_data;

  private $sortby;

  public function __construct(){}

  /**
   * Get YouTube API response as array, if order_by is specified result array
   * will be ordered accordingly
   * @param string $xml
   * @param string|bool $sortby
   */
  public function getParsedResponse($xml, $sortby = false) {
    libxml_use_internal_errors(true);
    $sxml = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    if (empty($errors)) {
      $this->getItems($sxml);
      if ($this->sortby = $sortby) {
        $items = $this->response_data['items'];
        if ($items) {
          usort($items, array($this, 'sortByFieldDesc'));
          $this->response_data['items'] = $items;
        }
      }
      return $this->response_data;
    }
    return false;
  }

  private function getItems($sxml) {
    $items = $sxml->entry;
    foreach ($items as $i) {
      $item = $this->getItemData($i);
      $media = $i->children('http://search.yahoo.com/mrss/');
      foreach ($media->group->thumbnail as $t) {
        $this->setItemThumbnail($item, 'thumbnail', $t);
      }
      $item['description'] = (string) $media->group->description;
      $this->response_data['items'][] = $item;
    }
  }

  private function getItemData($i) {
    $item = array();
    foreach ($i as $k => $v) {
      $method = 'setItem' . ucfirst($k);
      if (method_exists($this, $method)) {
        $this->$method($item, $k, $v);
      } else {
        if (!is_string($v)) $v = (string) $v;
        $item[$k] = $v;
      }
    }
    return $item;
  }

  private function setItemId(&$item, $k, $v) {
    // example id "tag:youtube.com,2008:video:5HU5_LptFl0"
    $parts = explode(':', $v);
    $item[$k] = end($parts);
  }

  private function setItemAuthor(&$item, $k, $v) {
    $item[$k] = array('name' => (string) $v->name,'uri' => (string) $v->uri);
  }

  private function setItemLink(&$item, $k, $v) {
    $item[$k][] = array(
      'rel' => $this->getAttrVal($v->attributes(), 'rel'),
      'href' => $this->getAttrVal($v->attributes(), 'href')
    );
  }

  private function setItemContent(&$item, $k, $v) {
    $item[$k] = $this->getAttrVal($v->attributes(), 'src');
  }

  private function setItemCategory(&$item, $k, $v) {
    $item[$k][] = array(
      'scheme' => $this->getAttrVal($v->attributes(), 'scheme'),
      'term' => $this->getAttrVal($v->attributes(), 'term')
    );
  }

  private function setItemThumbnail(&$item, $k, $v) {
    $item[$k][] = array(
      'url' => $this->getAttrVal($v->attributes(), 'url'),
      'height' => $this->getAttrVal($v->attributes(), 'height'),
      'width' => $this->getAttrVal($v->attributes(), 'width')
    );
  }

  /**
   * Sort by sortby property in descending order, currently only used for 
   * published field
   * @param array $a
   * @param array $b
   * @return number
   */
  private function sortByFieldDesc($a, $b) {
    $field = $this->sortby;
    if ($a[$field] == $b[$field]) {
      return 0;
    }
    return ($a[$field] > $b[$field]) ? -1 : 1;
  }
}