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
  <xsl:template match="@who">
    <xsl:attribute name="who">
      <xsl:value-of select="translate(., $uppercase, $lowercase)"/>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="@corresp">
    <xsl:attribute name="corresp">
      <xsl:value-of select="translate(., $uppercase, $lowercase)"/>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="tei:role/@xml:id">
    <xsl:attribute name="xml:id">
      <xsl:value-of select="translate(., $uppercase, $lowercase)"/>
    </xsl:attribute>
  </xsl:template>
</xsl:transform>
