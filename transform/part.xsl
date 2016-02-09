<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <xsl:output encoding="UTF-8" indent="yes" method="xml"/>
  <xsl:template match="node() | @*">
    <xsl:copy>
      <xsl:apply-templates select="node() | @*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:l">
    <xsl:variable name="prev" select="preceding::tei:l[1]"/>
    <xsl:variable name="next" select="following::tei:l[1]"/>
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:choose>
        <xsl:when test="@part = 'Y' and not($next/@part)">
          <xsl:attribute name="part">F</xsl:attribute>
        </xsl:when>
        <xsl:when test="@part = 'Y'">
          <xsl:attribute name="part">M</xsl:attribute>
        </xsl:when>
        <xsl:when test="not(@part) and $next/@part = 'Y'">
          <xsl:attribute name="part">I</xsl:attribute>
        </xsl:when>
      </xsl:choose>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>
