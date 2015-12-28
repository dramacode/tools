<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <xsl:output encoding="UTF-8" indent="yes" method="xml"/>
  <!-- Majuscules, pour conversions. -->
  <xsl:variable name="caps">ABCDEFGHIJKLMNOPQRSTUVWXYZÆŒÇÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝ</xsl:variable>
  <!-- Minuscules, pour conversions -->
  <xsl:variable name="mins">abcdefghijklmnopqrstuvwxyzæœçàáâãäåèéêëìíîïòóôõöùúûüý</xsl:variable>
  <xsl:template match="node() | @*">
    <xsl:copy>
      <xsl:apply-templates select="node() | @*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:speaker/text()">
    <xsl:value-of select="translate(., $mins, $caps)"/>
  </xsl:template>
  <xsl:template match="tei:body/tei:div | tei:body/tei:div1">
    <div1>
      <xsl:attribute name="type">act</xsl:attribute>
      <xsl:attribute name="xml:id">
        <xsl:number format="I"/>
      </xsl:attribute>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </div1>
  </xsl:template>
  <xsl:template match="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2">
    <div2>
      <xsl:attribute name="type">scene</xsl:attribute>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:body/tei:div | tei:body/tei:div1" format="I"/>
        <xsl:number format="01"/>
      </xsl:attribute>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </div2>
  </xsl:template>
  <xsl:template match="tei:sp">
    <xsl:copy>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:body/tei:div | tei:body/tei:div1" format="I"/>
        <xsl:number count="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2" format="01"/>
        <xsl:text>-</xsl:text>
        <xsl:number count="tei:sp"/>
      </xsl:attribute>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:l">
    <xsl:variable name="n">
      <xsl:number count="tei:l[not(@part)]" level="any"/>
    </xsl:variable>
    <xsl:copy>
      <xsl:attribute name="n">
        <xsl:value-of select="$n"/>
      </xsl:attribute>
      <xsl:if test="not(@part)">
        <xsl:attribute name="xml:id">
          <xsl:text>l</xsl:text>
          <xsl:value-of select="$n"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>
