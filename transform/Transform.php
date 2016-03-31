<?php
/**
Classe addhoc qui prend un fichier de Paul Fièvre pour en faire diverses choses
qui sera édité manuellement.
 */

set_time_limit(-1);
// included file, do nothing
if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) != basename(__FILE__));
else if (isset($_SERVER['ORIG_SCRIPT_FILENAME']) && realpath($_SERVER['ORIG_SCRIPT_FILENAME']) != realpath(__FILE__));
// direct command line call, work
else if (php_sapi_name() == "cli") Transform::cli();
/**
 * Class adhoc pour générer un docx à partir d’un XML/TEI
 */

class Transform {

  static function cli() {
    array_shift($_SERVER['argv']); // shift first arg, the script filepath
    $xslfile = 'identifier.xsl';
    if (!count($_SERVER['argv'])) exit("
Transformer une liste de fichier avec $xslfile\n\n");
    $xslfile = dirname(__FILE__).'/'.$xslfile;
    /*
    $flist=array("docx"=>"", "fix"=>"", "p5"=>"", "txt"=>"");
    if (!count($_SERVER['argv'])) exit('
usage    : php -f Transform.php (' . implode('|', array_keys($flist)) . ') tei.xml
    ');
    $format = rtrim(array_shift($_SERVER['argv']), '-');
    if (!isset($flist[$format])) exit('
format should one of : p5 txt docx
    ');
    */
    $xsl = new DOMDocument("1.0", "UTF-8");
    $xsl->load($xslfile);
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl);
    // appliquer une transformation comme filtre
    $i = 0;
    foreach ($_SERVER['argv'] as $glob) {
      foreach(glob($glob) as $srcfile) {
        $i++;
        $dom = self::dom($srcfile);
        $proc->transformToUri($dom, $srcfile);
      }
    }
  }
  static function dom($src, $xml="") {
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput=true;
    $dom->substituteEntities=true;
    if ($xml) $dom->loadXML($xml,  LIBXML_NOENT | LIBXML_NONET );
    else $dom->load($src,  LIBXML_NOENT | LIBXML_NONET );
    return $dom;
  }


  /**
   * Transformation xsl
   */
  static function xsl($xslFile, $dom, $dest=null, $pars=null) {
    $xsl = new DOMDocument("1.0", "UTF-8");
    $xsl->load($xslFile);
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl);
    // transpose params
    if($pars && count($pars)) foreach ($pars as $key => $value) $proc->setParameter('', $key, $value);
    // we should have no errors here
    if ($dest) $proc->transformToUri($dom, $dest);
    else return $proc->transformToXML($dom);
  }
}

?>
