<?php
/**
* ThumbConfig
* - An object to handle Thumbnail Configuration with sane defaults.
*
* ThumbConfig([Array options])
*   = Creates a ThumbConfig instance merging the given options onto the defaults.
*     Default available options are:
*       width: 140
*       height: 93
*       crop: true
*       quality: 75
*       forceDimensions: false
*
* ->#String :#Type
*   = Returns the value set for the #String if #String exists in the Config Object.
*
* ->#String=(#Type value)
*   = Sets the value set for the #String if #String exists in the Config Object.
*
*/
class ThumbConfig
{
  private $data;

  function __construct($options = Array()){
    $this->data = Array(
      'width'=>140,
      'height'=>93,
      'crop'=>true,
      'quality'=>75,
      'forceDimensions'=>false
    );
    $this->data = array_merge($this->data, $options);
  }
  public function __get($name){
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    return null;
  }
  public function __set($name, $value){
    if (array_key_exists($name, $this->data)) {
      $this->data[$name] = $value;
    }
  }
}
?>
