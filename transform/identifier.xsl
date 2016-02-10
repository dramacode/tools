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
  <xsl:key name="who" match="tei:role|tei:person" use="@xml:id"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:body/tei:div | tei:body/tei:div1">
    <div>
      <!-- Identifiant d’acte, repris ou construit -->
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
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$act"/>
      </xsl:attribute>
     <!-- toujours garder l’existant, si quelqu’un veut le changer avec cette XSL, le supprimer avant -->
     <xsl:copy-of select="@*"/>
      <xsl:apply-templates>
        <xsl:with-param name="act" select="$act"/>
      </xsl:apply-templates>
    </div>
  </xsl:template>
  <xsl:template match="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2">
    <xsl:param name="act"/>
    <div>
      <xsl:attribute name="type">scene</xsl:attribute>
      <!-- Identifiant de scène, repris ou construit -->
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
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$scene"/>
      </xsl:attribute>
      <!-- L’existant prime sur le généré -->
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates>
        <xsl:with-param name="scene" select="$scene"/>
      </xsl:apply-templates>
    </div>
  </xsl:template>
  <xsl:template match="tei:sp">
    <xsl:param name="scene"/>
    <xsl:copy>
      <!-- Identifiant de réplique, repris ou construit -->
      <xsl:variable name="id">
        <xsl:choose>
          <xsl:when test="@xml:id">
            <xsl:value-of select="@xml:id"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$scene"/>
            <xsl:text>-</xsl:text>
            <xsl:number count="tei:sp"/>
          </xsl:otherwise>
        </xsl:choose>  
      </xsl:variable>     
      <xsl:attribute name="xml:id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
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
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
</xsl:transform>