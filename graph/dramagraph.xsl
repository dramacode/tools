<?xml version="1.0" encoding="UTF-8"?>
<!-- 
TODO, automatiser la ligne temporelle en une passe ?
-->
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <!-- Grouper les répliques par rôle -->
  <xsl:key name="sp" match="tei:sp" use="@who"/>
  <!-- Lister les rôles en tête, pour des listes par scènes -->
  <xsl:key name="role" match="tei:front//tei:role" use="'all'"/>
  <!-- mode -->
  <xsl:param name="mode"/>
  <!-- constantes -->
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:variable name="tab" select="'&#13;'"/>
  <xsl:variable name="scene">scene</xsl:variable> 
  <!-- CSV -->
  <xsl:output method="text"/>
  <xsl:template match="/">
    <xsl:text>play</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>act</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>scene</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>sp</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>verses</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>words</xsl:text>
    <xsl:value-of select="$tab"/>
    <xsl:text>chars</xsl:text>
    <xsl:value-of select="$lf"/>
  </xsl:template>

  <xsl:template match="tei:div1-ok">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:number format="I"/>
      </xsl:attribute>
      <xsl:variable name="txt">
        <xsl:apply-templates mode="txt"/>
      </xsl:variable>
      <xsl:processing-instruction name="chars">
        <xsl:value-of select="string-length(normalize-space($txt))"/>
      </xsl:processing-instruction>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:div2">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:variable name="txt">
        <xsl:apply-templates mode="txt"/>
      </xsl:variable>
      <xsl:processing-instruction name="chars">
        <xsl:value-of select="string-length(normalize-space($txt))"/>
      </xsl:processing-instruction>
      <xsl:apply-templates/>
    </xsl:copy>    
  </xsl:template>
  <xsl:template match="tei:div2-ok">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:div1" format="I"/>
        <xsl:number format="01"/>
      </xsl:attribute>
      <xsl:variable name="txt">
        <xsl:apply-templates mode="txt"/>
      </xsl:variable>
      <xsl:processing-instruction name="chars">
        <xsl:value-of select="string-length(normalize-space($txt))"/>
      </xsl:processing-instruction>
      <xsl:apply-templates select="tei:head|processing-instruction()"/>
      <castList>
        <xsl:variable name="sp" select="tei:sp"/>
        <xsl:for-each select="key('role', 'all')">
          <xsl:variable name="id" select="@xml:id"/>
          <xsl:if test="$sp[@who=$id]">
            <castItem>
              <role>
                <xsl:attribute name="n">
                  <xsl:value-of select="$id"/>
                </xsl:attribute>
                <xsl:variable name="txt">
                  <xsl:apply-templates select="ancestor::tei:div2/tei:sp[@who=$id]" mode="txt"/>
                </xsl:variable>
                <xsl:processing-instruction name="chars">
                  <xsl:value-of select="string-length(normalize-space($txt))"/>
                </xsl:processing-instruction>
              </role>
            </castItem>
          </xsl:if>
        </xsl:for-each>
      </castList>
      <xsl:apply-templates select="*[name() != 'head']"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:sp-ok">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:div1" format="I"/>
        <xsl:number count="tei:div2" format="01"/>
        <xsl:text>-</xsl:text>
        <xsl:number count="tei:sp"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:p/@id | tei:s/@id ">
    <xsl:attribute name="n">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="tei:sp-ok">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <!--
      <xsl:variable name="txt">
        <xsl:apply-templates mode="txt"/>
      </xsl:variable>
      <xsl:processing-instruction name="chars">
        <xsl:value-of select="string-length(normalize-space($txt))"/>
      </xsl:processing-instruction>
      -->
      <xsl:processing-instruction name="start">
        <!-- Ne pas oublier les espaces intersticiels -->
        
        <xsl:value-of select="
  sum(../../preceding-sibling::tei:div1/processing-instruction('chars')) 
+ count(../../preceding-sibling::tei:div1) 
+ sum(../preceding-sibling::tei:div2/processing-instruction('chars')) 
+ count(../preceding-sibling::tei:div2)
+ sum(preceding-sibling::tei:sp/processing-instruction('chars'))
+ count(preceding-sibling::tei:sp)
        "/>
      </xsl:processing-instruction>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <!-- Count chars by sections -->
  <xsl:template match="*" mode="txt">
    <xsl:apply-templates select="*" mode="txt"/>
  </xsl:template>
  <xsl:template match="tei:p|tei:l" mode="txt">
    <xsl:value-of select="."/>
  </xsl:template>
  
</xsl:transform>