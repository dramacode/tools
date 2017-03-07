<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyzeéèêëoôöaâäiîïæœ'"/>
  <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZEÉÈÊËOÔÖAÂÄIÎÏÆŒ'"/>
  <xsl:template match="//tei:*[@type='scene'][not(@xml:id)]">
    <xsl:copy>
      <xsl:attribute name="xml:id"><xsl:value-of select="./parent::*[@type='act']/@xml:id"/><xsl:value-of select="@n"/></xsl:attribute>
      <xsl:apply-templates select="node()|@*"/>
      
    </xsl:copy>
  </xsl:template>
  
</xsl:transform>
