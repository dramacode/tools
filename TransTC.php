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
else if (php_sapi_name() == "cli") TransTC::cli();
/**
 * Class adhoc pour générer un docx à partir d’un XML/TEI
 */

class TransTC {
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
  static function cli() {
    array_shift($_SERVER['argv']); // shift first arg, the script filepath
    /*
    $flist=array("docx"=>"", "fix"=>"", "p5"=>"", "txt"=>"");
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
    */
    $xsl = new DOMDocument("1.0", "UTF-8");
    $xsl->load(dirname(__FILE__).'/tc2p5.xsl');
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl);
    // appliquer une transformation comme filtre
    $i = 0;
    foreach ($_SERVER['argv'] as $glob) {
      foreach(glob($glob) as $srcfile) {
        $i++;
        echo $i.'. '.$srcfile."\n";
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
