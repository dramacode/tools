<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
  >
  <xsl:strip-space elements="tei:TEI tei:TEI.2 tei:body tei:castList tei:div tei:div1 tei:div2  tei:docDate tei:docImprint tei:docTitle tei:fileDesc tei:front tei:group tei:index tei:listWit tei:p tei:publicationStmp tei:publicationStmt tei:sourceDesc tei:SourceDesc tei:sources tei:sp tei:text tei:teiHeader tei:text tei:titleStmt"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
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
  <xsl:template match="tei:note"/>
  <xsl:template match="tei:body/tei:div | tei:body/tei:div1">
    <div1>
      <xsl:attribute name="xml:id">
        <xsl:number format="I"/>
      </xsl:attribute>
      <!-- recouvrir les identifiants calculés par l’identifiant inscrit -->
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </div1>
  </xsl:template>
  <xsl:template match="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2">
    <div2>
      <xsl:apply-templates select="@*"/>
      <xsl:attribute name="type">scene</xsl:attribute>
      <xsl:attribute name="xml:id">
        <xsl:choose>
          <xsl:when test="parent::*[@xml:id]">
            <xsl:value-of select="parent::*[@xml:id]/@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:number count="tei:body/tei:div | tei:body/tei:div1" format="I"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:number format="01"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </div2>
  </xsl:template>
  <xsl:template match="tei:sp">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:choose>
          <xsl:when test="parent::*/parent::*[@xml:id]">
            <xsl:value-of select="parent::*/parent::*[@xml:id]/@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
           <xsl:number count="tei:body/tei:div | tei:body/tei:div1" format="I"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:number count="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2" format="01"/>
        <xsl:text>-</xsl:text>
        <xsl:number count="tei:sp"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <!-- Normalisation -->
  <!--
  <xsl:template match="tei:speaker/text()">
    <xsl:value-of select="translate(., $ABC, $abc)"/>
  </xsl:template>
  -->
  <xsl:template match="tei:p[.='']"/>
  <xsl:template match="tei:pb"/>
  <xsl:template match="tei:space"/>
  <!-- vers numérotation OK -->

  <xsl:template match="tei:l[ancestor::tei:body]">
    <xsl:copy>
      <xsl:copy-of select="@rend|@part"/>
      <xsl:variable name="n">
        <xsl:number count="tei:l[not(@part) or @part='I' or @part='i']" from="tei:body" level="any"/>
      </xsl:variable>
      <xsl:if test="not(@part) or @part='I' or @part='i'">
        <xsl:attribute name="n">
          <xsl:value-of select="$n"/>
        </xsl:attribute>
        <xsl:attribute name="xml:id">
          <xsl:text>l</xsl:text>
          <xsl:value-of select="$n"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>