<?xml version="1.0" encoding="UTF-8"?>
<!-- 
Ramasser des informations chiffrées d’une pièce 
-->
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
  >
  <!-- CSV -->
  <xsl:output method="text" encoding="UTF-8" indent="yes"/>
  <!-- Lister les rôles en tête, pour des listes par scènes -->
  <xsl:key name="role" match="tei:front//tei:role" use="@xml:id"/>
  <!-- Nom du fichier de pièce procédé -->
  <xsl:param name="filename"/>
  <!-- mode -->
  <xsl:param name="mode"/>
  <!-- constantes -->
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:variable name="tab" select="'&#09;'"/>
  <xsl:variable name="apos">'</xsl:variable>
  <xsl:variable name="quot">"</xsl:variable>
  <xsl:variable name="scene">scene</xsl:variable> 
  <xsl:template match="/">
    <xsl:text>filename</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>act</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>scene</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>sp</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>role</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>source</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>target</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>verses</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>words</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>chars</xsl:text>
    <xsl:value-of select="$lf"/>
    <xsl:apply-templates select="/*/tei:text/tei:body/*"/>
  </xsl:template>

  <!-- Acte -->
  <xsl:template match="tei:body/tei:div1 | tei:body/tei:div">
    <xsl:variable name="act">
      <xsl:choose>
        <xsl:when test="@xml:id">
          <xsl:value-of select="@xml:id"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:number format="I"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>   
    <xsl:apply-templates select="tei:div2|tei:div|tei:sp">
      <xsl:with-param name="act" select="$act"/>
    </xsl:apply-templates>
  </xsl:template>

  <!-- Scène -->
  <xsl:template match="tei:body/tei:div1/tei:div2 | tei:body/tei:div/tei:div">
    <xsl:param name="act"/>
    <xsl:variable name="scene">
      <xsl:choose>
        <xsl:when test="@xml:id">
          <xsl:value-of select="@xml:id"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$act"/>
          <xsl:number format="01"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:apply-templates select="tei:sp">
      <xsl:with-param name="act" select="$act"/>
      <xsl:with-param name="scene" select="$scene"/>
    </xsl:apply-templates>
  </xsl:template>

  <xsl:template match="tei:sp">
    <xsl:param name="act"/>
    <xsl:param name="scene"/>
    <xsl:value-of select="$filename"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="$act"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="$scene"/>
    <xsl:value-of select="$tab"/>
    <xsl:choose>
      <xsl:when test="@xml:id">
        <xsl:value-of select="@xml:id"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$scene"/>
        <xsl:text>-</xsl:text>
        <xsl:number/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="normalize-space(key('role', @who))"/>
    <xsl:value-of select="$tab"/>
    <!-- source -->
    <xsl:value-of select="@who"/>
    <xsl:value-of select="$tab"/>
    <!-- target -->
    <xsl:choose>
      <xsl:when test="preceding-sibling::tei:sp">
        <xsl:value-of select="preceding-sibling::tei:sp[1]/@who"/>
      </xsl:when>
      <xsl:when test="following-sibling::tei:sp">
        <xsl:value-of select="following-sibling::tei:sp[1]/@who"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="@who"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="count(.//tei:l)"/>
    <xsl:value-of select="$tab"/>
    <xsl:variable name="txt">
      <xsl:apply-templates select="*" mode="txt"/>
    </xsl:variable>
    <!-- Compter les mots, algo bête, nombre d’espaces et d’apostrophes  -->
    <xsl:value-of select="1 + string-length($txt) - string-length(translate($txt, concat(' ’', $apos), ''))"/>
    <xsl:value-of select="$tab"/>
    <xsl:value-of select="string-length($txt)"/>
    <xsl:value-of select="$tab"/>
    <xsl:text>"</xsl:text>
    <xsl:value-of select="translate($txt, $quot, '＂')"/>
    <xsl:text>"</xsl:text>
    <xsl:value-of select="$lf"/>
    
  </xsl:template>

  <!-- To Count chars -->
  <xsl:template match="tei:note|tei:stage|tei:speaker" mode="txt"/>
  <xsl:template match="tei:p|tei:l" mode="txt">
    <xsl:if test="preceding-sibling::tei:p or preceding-sibling::tei:l">
      <xsl:value-of select="$lf"/>
    </xsl:if>
    <xsl:variable name="txt">
      <xsl:apply-templates mode="txt"/>
    </xsl:variable>
    <xsl:value-of select="normalize-space($txt)"/>
  </xsl:template>
  
</xsl:transform>