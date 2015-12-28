<?php
echo "Charger un fichier UTF-8 avec SimpleXML et accéder aux attributs @xml:id\n<br/>";
libxml_use_internal_errors(true);
$xml= simplexml_load_file("moliere_avare.xml");
libxml_clear_errors();
explore_simplexml($xml);
exit();

function explore_simplexml($xml) {
  $id = $xml->attributes('xml', true)->id;
  if ($id) echo "\n".$xml->getName()." ".$id;
  foreach ($xml->children() as $child) {
    explore_simplexml($child);
  }
}


echo "Charger un fichier ISO avec SimpleXML\n";
$xml = simplexml_load_file("iso.xml"); 
echo "Le texte est en ISO\n";
echo $xml->asXML();


// passer par DOM
$doc = new DOMDocument();
echo "Charger un fichier UTF-8 avec DOM\n";
$doc->load("utf8.xml");
echo "Forcer l’encodage du DOM en ISO, charger le DOM dans SimpleXML \n";
$doc->encoding='ISO-8859-1';
$xml = simplexml_import_dom($doc); 
echo "Le texte est en ISO\n";
echo $xml->asXML();

echo "Charger un fichier ISO avec DOM\n";
$doc->load("iso.xml");
echo "Forcer l’encodage du DOM en ISO, charger le DOM dans SimpleXML \n";
$doc->encoding='ISO-8859-1';
$xml = simplexml_import_dom($doc); 
echo "Le texte est en ISO\n";
echo $xml->asXML();

?>