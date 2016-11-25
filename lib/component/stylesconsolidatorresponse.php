<?php
/**
 * ImageSet - responsive, lazy-loading images for Kirby CMS
 * 
 * @copyright (c) 2016 Fabian Michael <https://fabianmichael.de>
 * @link https://github.com/fabianmichael/kirby-imageset
 */

namespace Kirby\Plugins\ImageSet\Component;

use \Kirby\Component\Response;

/**
 * Collects inline styles generated by ImageSet, merges
 * them and places them in the head of HTML responses.
 * Consolidated styles are either placed before the first
 * occurance of a script element or — if there are no script
 * tags in the document head — directly before the closing
 * </head> tag.
 */
class StylesConsolidatorResponse extends Response {

  /**
   * Builds and return the response by various input
   * 
   * @param mixed $response
   * @return mixed
   */
  public function make($response) {
    $response = parent::make($response);

    if(stripos($response, '<!DOCTYPE html') !== 0) {
      // If document does not start with a doctype, assume
      // that it’s not an HTML document and just
      // pass-through the response.
      return $response;
    }

    $rules = [];
    $replace = [];
   
    preg_match_all('/\s*<style data-imagekit-styles>[\s\r\n]*(.*)[\s\r\n]*<\/style>\s*/siU', $response, $matches);

    if($matches) {
      
      foreach($matches[0] as $i => $match) {
        $replace[] = $match;
        $rules[]   = $matches[1][$i];
      }

      $styles   = '<style>' . implode('', $rules) . '</style>' . PHP_EOL;

      $scriptPos      = strpos($response, '<script');
      $headClosingPos = strpos($response, '</head>');
      $insertAt       = false;

      if($scriptPos !== false && $scriptPos < $headClosingPos) {
        $insertAt = $scriptPos;
      } else if($headClosingPos !== false) {
        $insertAt = $headClosingPos;
      }

      if($insertAt !== false) {
        $response = str_replace($replace, '', $response);
        $response = substr_replace($response, $styles, $insertAt, 0);
      }
    }

    return $response;
  }
}
