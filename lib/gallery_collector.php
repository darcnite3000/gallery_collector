<?php
require_once 'thumb_config.php';
require_once 'gallery_image.php';
/**
* Gallery Collector
* - A tool to generate a thumbnail gallery from a folder of images
* :Requires GalleryImage, ThumbConfig
*
* GalleryCollector(String filePath,[ String urlPath, [ ThumbConfig thumbConfig]])
*   = Creates a GalleryCollection Instance for the given file path
*
* ->build([Boolean clearCache]):String
*   = Creates a gallery building thumbs to the current thumbConfig settings
*     when required or if clearCache is true
*
* ->thumbConfig :ThumbConfig
*   = Returns the current thumbConfig.
*
* ->thumbConfig=(ThumbConfig newConfig)
*   = Sets the galleries thumbConfig.
*
* ->wrapTemplate :String
*   = Returns the current template that wraps the content items.
*   :Default -> '''
*               <ul class="{{wrapper_class}}">
*                 {{content}}
*               </ul>
*               '''
*
* ->wrapTemplate=(String templateString)
*   = Sets the gallery's content wrapping template it has two tags available:
*     {{output}}, the holder for the content items.
*     {{wrapper_class}}, the current ->wrapClass string.
*
* ->contentTemplate :String
*   = Returns the current template for the content items.
*   :Default -> '''
*               <li class="{{content_class}}">
*                 <a href="{{big_image}}"><img src="{{thumb_image}}"></a>
*               </li>
*               '''
*
* ->contentTemplate=(String templateString)
*   = Sets the gallery's content template it has four tags available:
*     {{content_class}}, the ->contentClass string.
*     {{big_image}} the content item's ->image_url string.
*     {{thumb_image}} the content item's ->thumb_url string.
*     {{image_name}} the content item's ->image_name string.
*
* ->wrapClass :String
*   = Returns the current class string that will be used in the template
*     on ->build() for {{wrap_class}}.
*   :Default -> 'gallery'
*
* ->wrapClass=(String classString)
*   = Sets the current class string that will be used in the template
*     on ->build() for {{wrap_class}}.
*
* ->contentClass :String
*   = Returns the current class string that will be used in the template
*     on ->build() for {{content_class}}.
*   :Default -> 'gallery-item'
*
* ->contentClass=(String classString)
*   = Sets the current class string that will be used in the template
*     on ->build() for {{content_class}}.
*/
class GalleryCollector{
  private $folder;
  private $images;
  private $wrapper_class;
  private $content_class;
  private $wrapper_template;
  private $content_template;
  private $thumb_config;


  function __construct($file_path, $url_path = "", $thumb_config = NULL){
    $this->images = Array();
    $this->folder = dir($file_path);
    if($thumb_config === NULL){
      $this->thumb_config = new ThumbConfig();
    }else{
      $this->thumb_config = $thumb_config;
    }
    $this->wrapper_class = "gallery";
    $this->content_class = "gallery-item";
    $this->wrapper_template = <<<EOT
<ul class="{{wrapper_class}}">
  {{content}}
</ul>
EOT;
    $this->content_template = <<<EOT
<li class="{{content_class}}">
  <a href="{{big_image}}"><img src="{{thumb_image}}"></a>
</li>
EOT;
    if($this->folder !== NULL && $this->folder !== false){
      while (false !== ($entry = $this->folder->read())) {
        if(preg_match("/\b(?P<name>.+)\.(?P<ext>png|jpeg|jpg)\b/i" , $entry, $matches) && preg_match('/\_thumb$/i', $matches['name'])==false){
          $image_name = $matches["name"];
          $extension = $matches["ext"];
          $this->images[] = new GalleryImage($this->folder,$url_path, $image_name, $extension);
        }
      }
    }
  }

  public function __get($name){
    $method_name = "_get_{$name}";
    if(method_exists($this, $method_name)){
      return $this->$method_name();
    }
  }
  public function __set($name, $value){
    $method_name = "_set_{$name}";
    if(method_exists($this, $method_name)){
      return $this->$method_name($value);
    }
  }

  public function build($clear_cache=false){
    $wrapper_output = $this->wrapper_template;
    $output = "";
    foreach ($this->images as $image) {
      $content_output = $this->content_template;
      $replacers = Array(
        "{{content_class}}",
        "{{big_image}}",
        "{{thumb_image}}",
        "{{image_name}}"
      );
      $replacments = Array(
        $this->content_class,
        $image->image_url,
        $image->thumb_url,
        $image->image_name
      );
      if($clear_cache || !$image->hasThumb()){
        $image->generateThumb($this->thumb_config);
      }
      $output.=str_replace($replacers, $replacments, $content_output);
    }
    $replacers = Array(
      "{{wrapper_class}}",
      "{{content}}"
    );
    $replacments = Array(
      $this->wrapper_class,
      $output
    );
    return str_replace($replacers, $replacments, $wrapper_output);
  }



  private function _set_thumbConfig($value){
    $this->thumb_config = $value;
  }
  private function _get_thumbConfig(){
    return $this->thumb_config;
  }
  private function _set_wrapTemplate($value){
    $this->wrapper_template = $value;
  }
  private function _get_wrapTemplate(){
    return $this->wrapper_template;
  }
  private function _set_contentTemplate($value){
    $this->content_template = $value;
  }
  private function _get_contentTemplate(){
    return $this->content_template;
  }


  private function _set_wrapClass($value){
    $this->wrapper_class = $value;
  }
  private function _get_wrapClass($value){
    return $this->wrapper_class;
  }
  private function _set_contentClass($value){
    $this->content_Class = $value;
  }
  private function _get_contentClass($value){
    return $this->content_class;
  }
}


?>
