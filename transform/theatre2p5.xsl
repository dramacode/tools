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
  <xsl:template match="tei:TEI.2">
    <xsl:processing-instruction name="xml-stylesheet">type="text/xsl" href="../Teinte/tei2html.xsl"</xsl:processing-instruction>
    <TEI>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </TEI>
  </xsl:template>
  <xsl:template match="tei:teiHeader">
    <xsl:copy>
      <xsl:apply-templates/>
      <profileDesc>
        <creation>
          <date>
            <xsl:attribute name="when">
              <!-- Année -->
              <xsl:choose>
                <xsl:when test="/*/tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when">
                  <xsl:value-of select="/*/tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when"/>
                </xsl:when>
                <xsl:when test="/*/tei:text/tei:front/tei:performance/tei:premiere/@date">
                  <xsl:value-of select="substring( normalize-space(/*/tei:text/tei:front/tei:performance/tei:premiere/@date), 1, 4)"/>
                </xsl:when>
                <xsl:when test="/*/tei:text/tei:front/tei:docDate/@value">
                  <xsl:value-of select="substring( normalize-space(/*/tei:text/tei:front/tei:docDate/@value), 1, 4)"/>
                </xsl:when>
                <xsl:when test="/*/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date">
                  <xsl:value-of select="substring( normalize-space(/*/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date), 1, 4)"/>
                </xsl:when>
                <xsl:otherwise>0000</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
          </date>
        </creation>
        <langUsage>
          <language ident="fr"/>
        </langUsage>
        <textClass>
          <keywords>
            <term type="genre">
              <xsl:variable name="genre">
                <xsl:value-of select="/*/tei:teiHeader/tei:fileDesc/tei:SourceDesc/tei:genre"/>
              </xsl:variable>
              <xsl:choose>
                <xsl:when test="$genre = 'Comédie'">
                  <xsl:attribute name="subtype">comedy</xsl:attribute>
                </xsl:when>
                <xsl:when test="$genre = 'Tragedy'">
                  <xsl:attribute name="subtype">tragedy</xsl:attribute>
                </xsl:when>
              </xsl:choose>
              <xsl:value-of select="$genre"/>
            </term>
          </keywords>
        </textClass>
      </profileDesc>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:SourceDesc">
    <sourceDesc>
      <xsl:apply-templates select="@*"/>
      <p>
        <xsl:if test="tei:permalien != ''">
          <ref target="{normalize-space(tei:permalien)}">
            <xsl:value-of select="normalize-space(tei:permalien)"/>
          </ref>
        </xsl:if>
      </p>
    </sourceDesc>
  </xsl:template>
  <xsl:template match="tei:publicationStmp">
    <publicationStmt>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </publicationStmt>
  </xsl:template>
  <xsl:template match="tei:div1">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:if test="@type = 'acte'">
        <xsl:attribute name="type">act</xsl:attribute>
      </xsl:if>
      <xsl:attribute name="xml:id">
        <xsl:number format="I"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:div2">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:div1" format="I"/>
        <xsl:number format="01"/>
      </xsl:attribute>
      <!--
      <xsl:apply-templates select="tei:head"/>
      <castList>
        <xsl:variable name="sp" select="tei:sp"/>
        <xsl:for-each select="key('role', 'all')">
          <xsl:variable name="id" select="@id"/>
          <xsl:if test="$sp[@who=$id]">
            <castItem>
              <role>
                <xsl:attribute name="corresp">
                  <xsl:text>#</xsl:text>
                  <xsl:value-of select="$id"/>
                </xsl:attribute>
                <xsl:apply-templates/>
              </role>
            </castItem>
          </xsl:if>
        </xsl:for-each>
      </castList>
      <xsl:apply-templates select="*[name() != 'head']"/>
      -->
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:sp">
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:attribute name="xml:id">
        <xsl:number count="tei:div1" format="I"/>
        <xsl:number count="tei:div2" format="01"/>
        <xsl:text>-</xsl:text>
        <xsl:number count="tei:sp"/>
      </xsl:attribute>
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
  <!-- supprimer des espaces en fin de bloc -->
  <xsl:template match="tei:castItem/text() | tei:head/text() | tei:l/text() | tei:note/text() | tei:s/text() | tei:stage/text()">
    <xsl:choose>
      <xsl:when test="following-sibling::node()">
        <xsl:copy/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="preceding-sibling::node() and starts-with(., ' ')">
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:value-of select="normalize-space(.)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="tei:speaker/text()">
    <xsl:value-of select="translate(normalize-space(.), $ABC, $abc)"/>
  </xsl:template>

  <!-- paragraphes à recompter après que le texte soit établi -->
  <xsl:template match="tei:p/@id | tei:s/@id"/>
  <xsl:template match="tei:role">
    <xsl:variable name="rend">
      <xsl:if test="@civil='M'"> male</xsl:if>
      <xsl:if test="@civil='F'"> female</xsl:if>
      <xsl:if test="@civil='G'"> group</xsl:if>
      <xsl:if test="@age='J'"> junior</xsl:if>
     <xsl:if test="@age='V'"> veteran</xsl:if>
    </xsl:variable>
    <xsl:copy>
      <xsl:if test="@id">
        <xsl:attribute name="xml:id">
          <xsl:value-of select="translate( ., $who1, $who2)"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:copy-of select="@xml:id"/>
      <xsl:if test="normalize-space($rend != '')">
        <xsl:attribute name="rend">
          <xsl:value-of select="normalize-space($rend)"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:copy-of select="@rend"/>
      <xsl:choose>
        <!-- Rôle en minuscules ou petites caps -->
        <xsl:when test="not(*)">
          <xsl:value-of select="substring(., 1, 1)"/>
          <xsl:value-of select="translate(substring(., 2), $ABC, $abc)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="@part">
    <xsl:attribute name="part">
      <xsl:choose>
        <xsl:when test=". = 'i'">I</xsl:when>
        <xsl:when test=". = 'f'">F</xsl:when>
        <xsl:when test=". = 'm'">F</xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="tei:pœm|tei:poem">
    <quote>
      <xsl:apply-templates/>
    </quote>
  </xsl:template>
  <xsl:template match="tei:bottom">
    <back>
      <xsl:apply-templates/>
    </back>
  </xsl:template>
  <!-- vers numérotation OK -->
  <xsl:template match="tei:l/@id">
    <xsl:attribute name="n">
      <xsl:value-of select="."/>
    </xsl:attribute>
    <xsl:if test="not(../@part) or (../@part='i')">
      <xsl:attribute name="xml:id">
        <xsl:text>l</xsl:text>
        <xsl:value-of select="."/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>
</xsl:transform>