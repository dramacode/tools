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
  <xsl:template match="tei:div[@type='scene']">
    
    <div2>
      <xsl:attribute name="xml:id">I<xsl:value-of select="count(preceding-sibling::tei:*[@type='scene'])+1"/></xsl:attribute>
      <xsl:attribute name="n"><xsl:value-of select="count(preceding-sibling::tei:*[@type='scene'])+1"/></xsl:attribute>
      <xsl:apply-templates select="node()|@*"/>
    </div2>
  </xsl:template>
  <xsl:template match="tei:sp">
    
    <sp>
      <xsl:attribute name="xml:id"><xsl:value-of select="parent::*/@xml:id"/>-<xsl:value-of select="count(preceding-sibling::tei:sp)+1"/></xsl:attribute>
      
      <xsl:apply-templates select="node()|@*"/>
    </sp>
  </xsl:template>
</xsl:transform>
