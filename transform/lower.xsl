<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
  >
  <xsl:strip-space elements="tei:TEI tei:TEI.2 tei:body tei:castList tei:div tei:div1 tei:div2  tei:docDate tei:docImprint tei:docTitle tei:fileDesc tei:front tei:group tei:index tei:listWit tei:p tei:publicationStmp tei:publicationStmt tei:sourceDesc tei:SourceDesc tei:sources tei:sp tei:text tei:teiHeader tei:text tei:titleStmt"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:variable name="who1">ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöùúûüý’' </xsl:variable>
  <xsl:variable name="who2">abcdefghijklmnopqrstuvwxyzaaaaaaceeeeiiiinooooouuuuyaaaaaaceeeeiiiinooooouuuuy---</xsl:variable>
  <xsl:variable name="ABC">ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ.</xsl:variable>
  <xsl:variable name="abc">abcdefghijklmnopqrstuvwxyzàáâãäåçèéêëìíîïñòóôõöùúûüý</xsl:variable>
  <xsl:key name="sp" match="tei:sp" use="@who"/>
  <!-- Lister les rôles en tête, pour des listes par scènes -->
  <xsl:key name="role" match="tei:front//tei:role" use="'all'"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:speaker/text()">
    <xsl:value-of select="translate(normalize-space(.), $ABC, $abc)"/>
  </xsl:template>
  <!-- paragraphes à recompter après que le texte soit établi -->
  <xsl:template match="tei:p/@id | tei:s/@id"/>
  <xsl:template match="tei:role/@id">
    <xsl:attribute name="xml:id">
      <xsl:value-of select="translate( ., $who1, $who2)"/>
    </xsl:attribute>  
  </xsl:template>
  <xsl:template match="tei:note"/>
  <xsl:template match="tei:sp">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:attribute name="who">
        <xsl:choose>
          <xsl:when test="@who">
            <xsl:value-of select="translate(@who, $who1, $who2)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="translate(tei:speaker, $who1, $who2)"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>