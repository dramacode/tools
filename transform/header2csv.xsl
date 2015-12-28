<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <xsl:output encoding="UTF-8" method="text" indent="no" omit-xml-declaration="yes"/>
  <xsl:param name="filename"></xsl:param>
  <xsl:variable name="tab"><xsl:text>	</xsl:text></xsl:variable>
  <xsl:variable name="lf"><xsl:text>
</xsl:text></xsl:variable>
  <!-- a priori, ne rien sortir -->
  <xsl:template match="text()"/>
  <xsl:template match="/*">
    <!-- Auteur -->
    <xsl:value-of select="substring-before(concat($filename,'_'), '_')"/>
    <xsl:value-of select="$tab"/>
    <!-- Titre -->
    <xsl:value-of select="substring-after($filename, '_')"/>
    <xsl:value-of select="$tab"/>
    <!-- Année -->
    <xsl:choose>
      <xsl:when test="tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when">
        <xsl:value-of select="tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when"/>
      </xsl:when>
      <xsl:when test="tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date">
        <xsl:value-of select="tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date"/>
      </xsl:when>
      <xsl:otherwise>0000</xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$tab"/>
    <!-- Type -->
    <xsl:variable name="type" select="tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:type"/>
    <xsl:choose>
      <xsl:when test="$type = 'vers'">v</xsl:when>
      <xsl:when test="$type = 'prose'">p</xsl:when>
      <xsl:when test="$type = 'mixte'">vp</xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$type"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$tab"/>
    <!-- Genre -->
    <xsl:variable name="genre" select="tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:genre"/>
    <xsl:choose>
      <xsl:when test="$genre = 'Tragi-comédie'">t</xsl:when>
      <xsl:when test="$genre = 'Tragédie'">t</xsl:when>
      <xsl:when test="$genre = 'Comédie'">c</xsl:when>
      <xsl:when test="$genre = 'Comédie-ballet'">c</xsl:when>
      <xsl:when test="$genre = 'Comédie galante'">c</xsl:when>
      <xsl:when test="$genre = 'Comédie héroïque'">t</xsl:when>
      <xsl:when test="$genre = 'Farce'">c</xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$genre"/>
      </xsl:otherwise>      
    </xsl:choose>
    <xsl:value-of select="$lf"/>
  </xsl:template>
</xsl:transform>
