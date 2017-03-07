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
  <xsl:template match="//tei:titleStmt/tei:author">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:choose>
        <xsl:when test="contains(., ',')">
          <surname>
            <xsl:value-of select="substring(substring-before(., ','), 1, 1)"/><xsl:value-of select="translate(substring(substring-before(., ','), 2), $uppercase, $lowercase)"/>
          </surname>
          <forename>
            <xsl:value-of select="substring-after(., ',')"/>
          </forename>
        </xsl:when>
        <xsl:otherwise><surname><xsl:value-of select="substring(., 1, 1)"/><xsl:value-of select="translate(substring(., 2), $uppercase, $lowercase)"/></surname></xsl:otherwise>
      </xsl:choose>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="//tei:titleStmt/tei:title">
    
    <xsl:choose>
      <xsl:when test="contains(., ',')">
        <title><xsl:value-of select="substring(substring-before(., ','), 1, 1)"/><xsl:value-of select="translate(substring(substring-before(., ','), 2), $uppercase, $lowercase)"/></title>
        <title type="sub">,<xsl:value-of select="substring(substring-after(., ','), 1, 1)"/><xsl:value-of select="translate(substring(substring-after(., ','), 2), $uppercase, $lowercase)"/></title>
      </xsl:when>
      <xsl:otherwise><title><xsl:value-of select="substring(., 1, 1)"/><xsl:value-of select="translate(substring(., 2), $uppercase, $lowercase)"/></title></xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:transform>
