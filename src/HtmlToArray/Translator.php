<?php
namespace HtmlToArray;
/**
 * Class for translate HTML to PHP array
 *
 * @author animir <animir@ya.ru>
 */
class Translator {    
    
    /**
     * Get SimpleXMLElement from URL content
     * 
     * @param string $url
     * @param string $encoding
     * @return SimpleXMLElement a SimpleXMLElement or <b>FALSE</b> on failure.
     */
    public function getXml($url, $encoding = 'UTF-8') {
        $doc = $this->prepareData($url, null, $encoding);
        return simplexml_import_dom($doc);
    }
    
    /**
     * Get DOMDocument from URL content
     * @param string $url
     * @param string $encoding
     * @return \DOMDocument
     */
    public function getDom($url, $encoding = 'UTF-8') {
        return $this->prepareData($url, null, $encoding);
    }
    
    /**
     * Get array from URL content
     * @param string $url
     * @param string $encoding
     * @return array
     */
    public function getArray($url, $encoding = 'UTF-8') {
        return $this->xml2array($this->getXml($url, $encoding));
    }

    /**
     * Get SimpleXMLElement from HTML string
     * 
     * @param string $html
     * @param string $encoding
     * @return SimpleXMLElement a SimpleXMLElement or <b>FALSE</b> on failure.
     */
    public function getXmlFromString($html, $encoding = 'UTF-8') {
        $doc = $this->prepareData(null, $html, $encoding);
        return simplexml_import_dom($doc);
    }
    
    /**
     * Get DOMDocument from HTML string
     * @param string $html
     * @param string $encoding
     * @return \DOMDocument
     */
    public function getDomFromString($html, $encoding = 'UTF-8') {
        return $this->prepareData(null, $html, $encoding);
    }
    
    /**
     * Get array from HTML string
     * @param string $html
     * @param string $encoding
     * @return array
     */
    public function getArrayFromString($html, $encoding = 'UTF-8') {
        return $this->xml2array($this->getXmlFromString($html, $encoding));
    }    
    
    private function array_map_recursive($func, $array) {
        foreach ($array as $key => $val) {
            if (is_array($array[$key])) {
                $array[$key] = array_map_recursive($func, $array[$key]);
            } else {
                $array[$key] = call_user_func($func, $val);
            }
        }
        return $array;
    }
    /**
     * Translate SimpleXMLElement to array
     * 
     * @param SimpleXMLElement $xml
     * @return array
     */
    public function xml2array($xml) {
        $fils = 0;
        $tab = false;
        $array = array();
        foreach ($xml->children() as $key => $value) {
            $child = $this->xml2array($value);

            foreach ($value->attributes() as $ak => $av) {
                $tmp = (array) $av;
                $child[$ak] = $tmp;
            }
            /**
             * @var $value \SimpleXMLElement
             */
            $child['text'] = $value->__toString();
            if (strlen(trim($child['text'])) === 0) {
                unset($child['text']);
            }

            if ($tab == false && in_array($key, array_keys($array))) {
                //If this element is already in the array we will create an indexed array
                $tmp = $array[$key];
                $array[$key] = NULL;
                $array[$key][] = $tmp;
                $array[$key][] = $child;
                $tab = true;
            } elseif ($tab == true) {
                $array[$key][] = $child;
            } else {
                $array[$key] = $child;
            }

            $fils++;
        }


        if ($fils == 0) {
            $tmp = (array) $xml;       
            return $tmp;
        }

        return $array;
    }
    /**
     * Replace some attributes that important for rendering only, script, style and other tags.
     * Convert HTML from $encoding to UTF-8 charset.
     * 
     * @param string $html
     * @param string $encoding
     * @return string
     */
    private function prepareForDOM($html, $encoding) {
        $html = iconv($encoding, 'UTF-8//TRANSLIT', $html);
        $html = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1\b[^>]*>/is', '', $html);
        $tidy = new \tidy;
        $config = array(
            'drop-font-tags' => true,
            'drop-proprietary-attributes' => true,
            'hide-comments' => true,
            'indent' => true,
            'logical-emphasis' => true,
            'numeric-entities' => true,
            'output-xhtml' => true,
            'wrap' => 0
        );
        $tidy->parseString($html, $config, 'utf8');
        $tidy->cleanRepair();
        $html = $tidy->value;
        $html = preg_replace('#<meta[^>]+>#isu', '', $html);
        $html = preg_replace('#<head\b[^>]*>#isu', "<head>\r\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />", $html);
        return $html;
    }
    
    /**
     * Prepare DOM from HTML
     * @param string $url
     * @param string $html
     * @param string $encoding
     * @return \DOMDocument
     */
    private function prepareData($url, $html = null, $encoding = 'UTF-8') {
        if (is_null($html)) {
            $html = file_get_contents($url);
        } 
        $html = $this->prepareForDOM($html, $encoding);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        //@ for non-valid html
        @$doc->loadHTML($html);
        
        return $doc;
    }

}

