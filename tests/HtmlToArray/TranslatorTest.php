<?php
namespace HtmlToArray;

//use HtmlToArray\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase {
    public function testTranslate() {
        $translator = new Translator();
        $html = <<<HTML
<html>
   <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0"> 

     <title>PHP: Hypertext Preprocessor</title>

 <link rel="shortcut icon" href="http://php.net/favicon.ico">
 <link rel="search" type="application/opensearchdescription+xml" href="http://php.net/phpnetimprovedsearch.src" title="Add PHP.net search">
 <link rel="alternate" type="application/atom+xml" href="http://php.net/releases/feed.php" title="PHP Release feed">
 <link rel="alternate" type="application/atom+xml" href="http://php.net/feed.atom" title="PHP: Hypertext Preprocessor">

 <link rel="canonical" href="http://php.net/index.php">
 <link rel="shorturl" href="http://php.net/index">
 <link rel="alternate" href="http://php.net/index" hreflang="x-default">
   </head>
   <body>       
    <ul class="nav">
      <li class=""><a href="/downloads">Downloads</a></li>
      <li class=""><a href="/docs.php">Documentation</a></li>
      <li class=""><a href="/get-involved" >Get Involved</a></li>
      <li class=""><a href="/support">Help</a></li>
    </ul>
   </body>
</html>
HTML;
    
    $array = $translator->getArrayFromString($html);
    //check attributes parse
    $this->assertEquals('/downloads', $array['body']['ul']['li']['0']['a']['@attributes']['href']);
    
    //check text parse
    $this->assertEquals('Documentation', $array['body']['ul']['li']['1']['a']['text']);
    }
}