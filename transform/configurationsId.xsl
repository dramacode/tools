<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:key name="configuration" match="tei:sp" use="generate-id(preceding-sibling::tei:listPerson)"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  <!-- ajouter la liste des person entre deux listPerson -->
  <xsl:template match="tei:listPerson[@type='configuration']/@xml:id">
   
      <xsl:attribute name="xml:id">conf<xsl:value-of select="count(preceding::tei:listPerson[@type='configuration'])+1"/> </xsl:attribute>
      <!--<xsl:apply-templates select="node()|@*"/>-->
    
  </xsl:template>
</xsl:transform>
