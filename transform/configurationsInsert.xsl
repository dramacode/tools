<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform exclude-result-prefixes="tei" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output encoding="UTF-8" indent="yes" method="xml"/>
  <xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyzeeeeeooaaaaiiiæœeeeeaaii'"/>
  <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZEÉÈÊËOÔÖAÂÄIÎÏÆŒéèêëâäîï'"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:body//*[@type='act']/*[@type='scene']/tei:head">
    <xsl:variable name="scene" select="parent::*"/>
    <xsl:variable name="head">
      <xsl:choose>
        <xsl:when test="$scene/tei:head/following-sibling::*[1][self::tei:stage]">
          <xsl:value-of select="$scene/tei:head/following-sibling::*[1][self::tei:stage]"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$scene/tei:head"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="n" select="count(preceding::tei:head[ancestor::tei:*[@type='act']])"/>
    <xsl:copy>
      <xsl:apply-templates/>
      <listPerson type="configuration">
        <xsl:attribute name="xml:id">conf<xsl:value-of select="$n"/></xsl:attribute>
        <xsl:for-each select="parent::*/tei:sp">
          <xsl:if test="not(preceding-sibling::tei:sp/@who = current()/@who)">
            <person corresp="#{@who}"/>
          </xsl:if>
        </xsl:for-each>
        <xsl:for-each select="//tei:castList//tei:role">
          <xsl:variable name="id">
            <xsl:choose>
              <xsl:when test="@xml:id">
                <xsl:value-of select="@xml:id"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:value-of select="@id"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:variable>
          <xsl:if test="(contains(normalize-space(translate($head, $uppercase, $lowercase)), normalize-space(translate(current()/text(), $uppercase, $lowercase)))) and (count($scene/tei:sp[@who= $id])=0)">
            <person corresp="#{$id}"/>
          </xsl:if>
        </xsl:for-each>
      </listPerson>
    </xsl:copy>
  </xsl:template>
</xsl:transform>
