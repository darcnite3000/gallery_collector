<?php
require_once 'thumb_config.php';
/**
* GalleryImage
* - An object that represents an image within a gallery, with the ability to
*   generate a thumbnail for the image.
* :Requires ThumbConfig
*
* GalleryImage(Directory folder, String urlPath, String imageName, String extension)
*   = Builds a GalleryImage instance to represent an image.
*
* ->image_name :String
*   = Returns the imageName set on creation.
*
* ->image_url :String
*   = Returns the url for the image that could be used as an image src.
*
* ->image_path :String
*   = Returns the path to the image in the filesystem.
*
* ->thumb_url :String
*   = Returns the url for the image's thumbnail
*     that could be used as an image src.
*
* ->thumb_path :String
*   = Returns the path to the image's thumbnail in the filesystem.
*
* ->hasThumb :Boolean
*   = Returns the status of if the image's thumbnail exists in the filesystem.
*
* ->generateThumb(ThumbConfig config)
*   = Creates a thumbnail image for the given config deleting an existing thumb
*     if one already exists.
*     This uses the ThumbConfig setting for height/width and will flip these
*     for the alternate mode(landscape/portrait) unless forceDimensions is set.
*     It also uses the ThumbConfig setting for quality in the generated thumb.
*/
class GalleryImage{
  private $folder;
  private $url_path;
  private $image_name;
  private $extension;

  function __construct($folder, $url_path, $image_name, $extension){
    $this->folder = $folder;
    $this->url_path = $url_path;
    $this->image_name = $image_name;
    $this->extension = $extension;
  }

  private function _get_image_name(){
    return $this->image_name;
  }
  private function _get_image_url(){
    return "{$this->url_path}/{$this->image_name}.{$this->extension}";
  }
  private function _get_image_path(){
    return "{$this->folder->path}/{$this->image_name}.{$this->extension}";
  }
  private function _get_thumb_url(){
    return "{$this->url_path}/{$this->image_name}_thumb.{$this->extension}";
  }
  private function _get_thumb_path(){
    return "{$this->folder->path}/{$this->image_name}_thumb.{$this->extension}";
  }

  public function hasThumb(){
    return file_exists($this->thumb_path);
  }

  public function generateThumb($config){
    switch (strtolower($this->extension)) {
      case 'jpg':
      case 'jpeg':
        $this->generateJPEGThumb($config);
        break;
      case 'png':
        $this->generatePNGThumb($config);
        break;
    }
  }

  private function generateJPEGThumb($config){
    $in_image = @imagecreatefromjpeg($this->image_path);
    if($in_image){
      $out = $this->createThumbImage($config, $in_image);
      if($out!==false){
        if($this->hasThumb()) unlink($this->thumb_path);
        imagejpeg($out, $this->thumb_path,$config->quality);
        imagedestroy($out);
      }
      imagedestroy($in_image);
    }
  }
  private function generatePNGThumb($config){
    $in_image = @imagecreatefrompng($this->image_path);
    if($in_image){
      $out = $this->createThumbImage($config, $in_image);
      if($out!==false){
        if($this->hasThumb()) unlink($this->thumb_path);
        imagepng($out, $this->thumb_path,$config->quality);
        imagedestroy($out);
      }
      imagedestroy($in_image);
    }
  }


  private function createThumbImage($config, &$in_image){
      $in_width = imagesx($in_image);
      $in_height = imagesy($in_image);

      $image_isLandscape = ($in_width>$in_height);
      $config_isLandscape = ($config->width>$config->height);
      if(!$config->forceDimensions && ($image_isLandscape == !$config_isLandscape)){
        $width = $config->height;
        $height = $config->width;
      }else{
        $width = $config->width;
        $height = $config->height;
      }

      $width_ratio = $width / $in_width;
      $height_ratio = $height / $in_height;
      if ($config->crop) {
        if ($width_ratio > $height_ratio) {
          $ratio = $width_ratio;
        } else {
          $ratio = $height_ratio;
        }
        $out_width = $in_width * $ratio;
        $out_height = $in_height * $ratio;
        $out = imagecreatetruecolor($width, $height);
        imagecopyresampled($out, $in_image, ($width - $out_width) / 2, ($height - $out_height) / 2, 0, 0, $out_width, $out_height, $in_width, $in_height);
      } else {
        if ($width_ratio < $height_ratio) {
          $ratio = $width_ratio;
        } else {
          $ratio = $height_ratio;
        }
        $out_width = $in_width * $ratio;
        $out_height = $in_height * $ratio;
        $out = imagecreatetruecolor($out_width, $out_height);
        imagecopyresampled($out, $in_image, 0, 0, 0, 0, $out_width, $out_height, $in_width, $in_height);
      }
      return $out;
  }

  static function cmp_obj($a, $b)
  {
      $al = strtolower($a->image_name);
      $bl = strtolower($b->image_name);
      if ($al == $bl) {
          return 0;
      }
      return ($al > $bl) ? +1 : -1;
  }

  public function __get($name){
    $method_name = "_get_{$name}";
    if(method_exists($this, $method_name)){
      return $this->$method_name();
    }
  }

  public function __toString(){
    return '<img src="'.$this->image_url.'">';
  }
}
?>
