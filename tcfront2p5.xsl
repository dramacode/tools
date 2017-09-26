<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:strip-space elements="tei:TEI tei:TEI.2 tei:body tei:castList tei:div tei:div1 tei:div2  tei:docDate tei:docImprint tei:docTitle tei:fileDesc tei:front tei:group tei:index tei:listWit tei:p tei:publicationStmp tei:publicationStmt tei:sourceDesc tei:SourceDesc tei:sources tei:sp tei:text tei:teiHeader tei:text tei:titleStmt"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:front[tei:docTitle]">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <titlePage>
        <xsl:apply-templates select="*[not(self::tei:div)][not(self::tei:set)][not(self::tei:castList)]"/>
      </titlePage>
      <xsl:apply-templates select="tei:div"/>
      <div xml:id="castList">
        <xsl:apply-templates select="tei:castList"/>
        <xsl:apply-templates select="tei:set"/>
      </div>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="@type[.='dedicace']">
    <xsl:attribute name="type">dedication</xsl:attribute>
  </xsl:template>
  <xsl:template match="tei:role">
    <xsl:copy>
      <xsl:copy-of select="@xml:id"/>
      <xsl:attribute name="rend">
        <xsl:variable name="string">
          <xsl:choose>
            <xsl:when test="@civil = 'M'">male</xsl:when>
            <xsl:when test="@civil = 'F'">female</xsl:when>
            <xsl:when test="@sex = 1">male</xsl:when>
            <xsl:when test="@sex = 2">female</xsl:when>
          </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="normalize-space( $string )"/>
      </xsl:attribute>
      <xsl:copy-of select="@rend"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>
