<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform exclude-result-prefixes="tei" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:strip-space elements="tei:TEI tei:TEI.2 tei:body tei:castList tei:div tei:div1 tei:div2  tei:docDate tei:docImprint tei:docTitle tei:fileDesc tei:front tei:group tei:index tei:listWit tei:p tei:publicationStmp tei:publicationStmt tei:sourceDesc tei:SourceDesc tei:sources tei:sp tei:text tei:teiHeader tei:text tei:titleStmt"/>
  <xsl:output encoding="UTF-8" indent="yes" method="xml"/>
  <xsl:variable name="ABC">ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ.</xsl:variable>
  <xsl:variable name="abc">abcdefghijklmnopqrstuvwxyzàáâãäåçèéêëìíîïñòóôõöùúûüý</xsl:variable>
  <xsl:key match="tei:sp" name="sp" use="@who"/>
  <!-- Lister les rôles en tête, pour des listes par scènes -->
  <xsl:key match="tei:role|tei:person" name="who" use="@xml:id"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
  </xsl:template>
  <!-- Identification des configurations -->
  <xsl:template match="tei:listPerson[@type='configuration']">
    <xsl:copy>
      <xsl:attribute name="xml:id">
        <xsl:text>conf</xsl:text>
        <xsl:number count="tei:listPerson[@type='configuration']" level="any" from="tei:body"/>
      </xsl:attribute>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:body/tei:div[@type='act'] | tei:body/tei:div1[@type='act']">
    <xsl:copy>
      <!-- Identifiant d’acte, repris ou construit -->
      <xsl:variable name="id">
        <xsl:choose>
          <xsl:when test="@xml:id">
            <xsl:value-of select="@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:number count="tei:div[@type='act']|tei:div1[@type='act']" format="I"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
      <!-- toujours garder l’existant, si quelqu’un veut le changer avec cette XSL, le supprimer avant -->
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates>
        <xsl:with-param name="parent" select="$id"/>
      </xsl:apply-templates>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:body/tei:div[@type='act']/tei:div | tei:body/tei:div1[@type='act']/tei:div2">
    <xsl:param name="parent"/>
    <xsl:copy>
      <xsl:attribute name="type">scene</xsl:attribute>
      <!-- Identifiant de scène, repris ou construit -->
      <xsl:variable name="id">
        <xsl:choose>
          <xsl:when test="@xml:id">
            <xsl:value-of select="@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$parent"/>
            <xsl:number format="01"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
      <!-- L’existant prime sur le généré -->
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates>
        <xsl:with-param name="parent" select="$id"/>
      </xsl:apply-templates>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:sp[ancestor::tei:body]">
    <xsl:param name="parent"/>
    <xsl:copy>
      <!-- Identifiant de réplique, repris ou construit -->
      <xsl:variable name="id">
        <xsl:choose>
          <xsl:when test="@xml:id">
            <xsl:value-of select="@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$parent"/>
            <xsl:text>-</xsl:text>
            <xsl:number count="tei:sp"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
      <!-- Les valeurs présentes prennent le dessus -->
      <xsl:copy-of select="@*"/>
      <!-- Tester au moins le premier who -->
      <xsl:variable name="who1" select="substring-before(concat(@who, ' '), ' ')"/>
      <xsl:choose>
        <xsl:when test="not(@who)">
          <xsl:message>#<xsl:value-of select="$id"/> sp/@who ?</xsl:message>
        </xsl:when>
        <xsl:when test="not(key('who', $who1))">
          <xsl:message>#<xsl:value-of select="$id"/>, "<xsl:value-of select="@who"/>", pas de @xml:id pour ce rôle</xsl:message>
        </xsl:when>
      </xsl:choose>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <!-- Numérotation des vers, le process suppose ici que tous les vers dits sont dans le <body>, s’il y en 
  a en <front>, ce sont des vers cités dans l’instroduction critique (cf. Bibliothèque dramatique) -->
  <xsl:template match="tei:l[ancestor::tei:body]">
    <xsl:copy>
      <xsl:variable name="n">
        <xsl:number count="tei:l[(not(@part) or @part='I' or @part='i') and normalize-space(.) != '']" from="tei:body" level="any"/>
      </xsl:variable>
      <xsl:if test="(not(@part) or @part='I' or @part='i') and normalize-space(.) != ''">
        <xsl:attribute name="n">
          <xsl:value-of select="$n"/>
        </xsl:attribute>
        <xsl:attribute name="xml:id">
          <xsl:text>l</xsl:text>
          <xsl:value-of select="$n"/>
        </xsl:attribute>
      </xsl:if>
      <!-- Les valeurs présentes prennent le dessus -->
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>
