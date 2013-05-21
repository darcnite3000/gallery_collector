GalleryCollector
================

A PHP tool to turn a folder into a thumbnail gallery

```PHP
include 'gallery_collector.php';
$gallery = new GalleryCollector("gallery","gallery");
echo $gallery->build();
```