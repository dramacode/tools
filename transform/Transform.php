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
else if (php_sapi_name() == "cli") Transform::doCli();
/**
 * Class adhoc pour générer un docx à partir d’un XML/TEI
 */

class Transform {
  static $re=array(
    '@([:;!?»])@u' => ' $1',
    '@[  ]+ @u' => ' ',
    '@ (:[^=<>\s]+=")@u' => '$1',
    '@< ([\?!])@' => '<$1',
    '@ ([\?!])>@' => '$1>',
    '@ ://@' => '://',
    '@oe@' => 'œ',
    "@'@" => '’',
    '@&([a-z0-9]+) ;@' => '&$1;',
    '@<head>(SC[ÈE]NE ([IVX]+|PREMI[ÈE]RE|SECONDE|TROISI[ÉÈ]ME|QUATRI[ÉÈ]ME))\.? (.+)</head>@u' => "<head>$1</head>\n          <stage>$3</stage>",
    '@\s+</(head|s|stage)>@u' => '',
    '@([A-ZÇÂÉÈËÎÏŒ])\.</(speaker|head)>@u' => '$1</$2>',
  );
  static function doCli() {
    $flist=array("docx"=>"", "fix"=>"", "p5"=>"", "txt"=>"");
    array_shift($_SERVER['argv']); // shift first arg, the script filepath
    if (!count($_SERVER['argv'])) exit('
usage    : php -f Transform.php (' . implode('|', array_keys($flist)) . ') tei.xml
    ');
    $format = rtrim(array_shift($_SERVER['argv']), '-');
    if (!isset($flist[$format])) exit('
format should one of : p5 txt docx
    ');
    if (!count($_SERVER['argv'])) exit('
A filepath (or a glob) is needed for transform      
    ');
    foreach (glob(array_shift($_SERVER['argv'])) as $src) {
      $destname=dirname($src).'/'.pathinfo($src, PATHINFO_FILENAME);

      if ($format == 'p5') {
        self::teip5($src);
      }
      else if ($format == 'fix') {
        $dom = self::dom($src);
        $xml=self::xsl(dirname(__FILE__).'/fix.xsl', $dom);
        file_put_contents($src, $xml);
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
  static function teip5($src) {
    // 1) P5quifier le TEI de Paul
    $xml = file_get_contents($src);
    if (($pos = strpos($xml, "<TEI.2>")) === false) return;
    echo "\n".$src;
    $xml = substr($xml, 0, $pos) . '<TEI xmlns="http://www.tei-c.org/ns/1.0">' . substr($xml, $pos + 7, strpos($xml, "</TEI.2>") - $pos - 7) . "</TEI>";
    $dom = self::dom(null, $xml);
    $xml=self::xsl(dirname(__FILE__).'/theatre2p5.xsl', $dom);
    $xml=preg_replace(array_keys(self::$re), array_values(self::$re), $xml);
    file_put_contents($src, $xml);
  }
  static function docx($teip5, $dest) {
    $dom=new DOMDocument("1.0", "UTF-8");
    $dom->loadXML($xml);
    copy(dirname(__FILE__).'/template.docx', $dest);
    $document=self::xsl(dirname(__FILE__).'/tei2docx.xsl', $dom);
    $footnotes=self::xsl(dirname(__FILE__).'/tei2docx-fn.xsl', $dom);
    // file_put_contents(dirname($src).'/'.pathinfo($src, PATHINFO_FILENAME).'docx.xml', $document );
    self::insert($dest, $document, $footnotes);
  }
  /**
   * Insérer un nouveau document dans un template
   */
  static function insert($docx, $document, $footnotes=null) {
    $zip = new ZipArchive;
    $zip->open($docx);
    $zip->addFromString('word/document.xml', $document);
    if ($footnotes) $zip->addFromString('word/footnotes.xml', $footnotes);
    $zip->close();
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