<?php
// declare(encoding = 'utf-8');
setlocale(LC_ALL, 'fr_FR.utf8');
mb_internal_encoding("UTF-8");
// vérifier la validité des @who
$drama = new Dramagraph("moliere_1664_tartuffe.xml");
$drama->validwho();
class Dramagraph {
  /** Document XML */
  private $_dom;
  /** Processeur xpath */
  private $_xpath;
  /** Liste des identifiants @who */
  private $_castlist = array();

  /**
   * Charger un fichier XML
   */
  function __construct($file) {
    $this->_dom = new DOMDocument();
    $this->_dom->load($file, LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING);
    $this->_xpath = new DOMXpath($this->_dom);
    $this->_xpath->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");
  }
  /**
   * Collecter les identifiants dans les <role>
   * Alerter sur les iddentifiants 
   */
  public function validwho() {
    $nodes = $this->_xpath->query("//tei:role/@xml:id");
    foreach ($nodes as $n) {
      $_castlist[$n->nodeValue] = true;
    }
    $nodes = $this->_xpath->query("//@who");
    foreach ($nodes as $n) {
      $who = $n->nodeValue;
      if (isset($_castlist[$who])) continue;
      echo $who.' l. '.$n->getLineNo();
    }
  }

}
?>