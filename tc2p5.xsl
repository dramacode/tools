<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:strip-space elements="tei:TEI tei:TEI.2 tei:body tei:castList tei:div tei:div1 tei:div2  tei:docDate tei:docImprint tei:docTitle tei:fileDesc tei:front tei:group tei:index tei:listWit tei:p tei:publicationStmp tei:publicationStmt tei:sourceDesc tei:SourceDesc tei:sources tei:sp tei:text tei:teiHeader tei:text tei:titleStmt"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:variable name="lf" select="'&#10;'"/>
  <xsl:variable name="UC"
    >ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöùúûüýœ’' </xsl:variable>
  <xsl:variable name="lc"
    >abcdefghijklmnopqrstuvwxyzaaaaaaceeeeiiiin?ooooouuuuyaaaaaaceeeeiiiinooooouuuuy?---</xsl:variable>
  <xsl:variable name="who1"
    >ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ’' </xsl:variable>
  <xsl:variable name="who2"
    >AAAAAACEEEEIIIINOOOOOUUUUY</xsl:variable>
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
  <xsl:template match="/*">
    <xsl:processing-instruction name="xml-stylesheet">type="text/xsl" href="../Teinte/tei2html.xsl"</xsl:processing-instruction>
    <xsl:value-of select="$lf"/>
    <xsl:processing-instruction name="xml-model">href="http://oeuvres.github.io/Teinte/teinte.rng" type="application/xml" schematypens="http://relaxng.org/ns/structure/1.0"</xsl:processing-instruction>
    <xsl:processing-instruction name="xml-model">href="http://oeuvres.github.io/Teinte/teinte.sch" type="application/xml" schematypens="http://purl.oclc.org/dsdl/schematron"</xsl:processing-instruction>
    <xsl:value-of select="$lf"/>
    <xsl:copy>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:teiHeader">
    <xsl:copy>
      <xsl:apply-templates/>
      <profileDesc>
        <creation>
          <xsl:variable name="published">
            <xsl:choose>
              <xsl:when test="/*/tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when">
                <xsl:value-of select="/*/tei:teiHeader/tei:profileDesc/tei:creation/tei:date/@when"/>
              </xsl:when>
              <xsl:when test="/*/tei:text/tei:front/tei:docDate/@value">
                <xsl:value-of select="normalize-space(/*/tei:text/tei:front/tei:docDate/@value)"/>
              </xsl:when>
              <xsl:when test="/*/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date">
                <xsl:value-of select=" normalize-space(/*/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:date)"/>
              </xsl:when>
            </xsl:choose>
          </xsl:variable>
          <xsl:if test="$published != ''">
            <date type="published" when="{$published}"/>
          </xsl:if>
          <xsl:if test="/*/tei:text/tei:front/tei:performance/tei:premiere/@date">
            <date type="performed" when="{/*/tei:text/tei:front/tei:performance/tei:premiere/@date}"/>
          </xsl:if>
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
                <xsl:when test="$genre = 'Tragédie'">
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
      <xsl:apply-templates/>
    </sourceDesc>
  </xsl:template>

  <xsl:template match="tei:publicationStmp">
    <publicationStmt>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </publicationStmt>
  </xsl:template>
  <xsl:template match="tei:body/tei:div | tei:body/tei:div1">
    <div1>
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
      <xsl:if test="@type='acte'">
        <xsl:attribute name="type">act</xsl:attribute>        
      </xsl:if>
      <xsl:apply-templates>
        <xsl:with-param name="act" select="$act"/>
      </xsl:apply-templates>
    </div1>
  </xsl:template>
  <xsl:template match="tei:body/tei:div/tei:div | tei:body/tei:div1/tei:div2">
    <xsl:param name="act"/>
    <div2>
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
      <xsl:if test="@type='scène'">
        <xsl:attribute name="type">scene</xsl:attribute>        
      </xsl:if>
      <xsl:apply-templates>
        <xsl:with-param name="scene" select="$scene"/>
      </xsl:apply-templates>
    </div2>
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
      <!--
      <xsl:choose>
        <xsl:when test="not(@who)">
          <xsl:message>#<xsl:value-of select="$id"/> sp/@who ?</xsl:message>
        </xsl:when>
        <xsl:when test="not(key('who', $who1))">
          <xsl:message>#<xsl:value-of select="$id"/>, "<xsl:value-of select="@who"/>", pas de @xml:id pour ce rôle</xsl:message>
        </xsl:when>
      </xsl:choose>
      -->
      <xsl:apply-templates/>
    </xsl:copy>
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
      <xsl:if test="normalize-space($rend) != ''">
        <xsl:attribute name="rend">
          <xsl:value-of select="normalize-space($rend)"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="@part">
    <xsl:attribute name="part">
      <xsl:choose>
        <xsl:when test=". = 'i'">I</xsl:when>
        <xsl:when test=". = 'f'">F</xsl:when>
        <xsl:when test=". = 'm'">M</xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="tei:pœm|tei:poem">
    <lg>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </lg>
  </xsl:template>
  <xsl:template match="tei:bottom">
    <back>
      <xsl:apply-templates/>
    </back>
  </xsl:template>
  <xsl:template match="tei:p/@type[.='p']"/>
  <xsl:template match="tei:front//tei:p[@type='v']">
    <l>
      <xsl:apply-templates/>
    </l>
  </xsl:template>
  <!-- vers numérotation OK -->
  <xsl:template match="tei:l">
    <xsl:copy>
      <xsl:variable name="n" select="@id"/>
      <xsl:choose>
        <xsl:when test=" @part = 'f' or @part = 'F' or @part = 'm' or @part = 'M' "/>
        <xsl:otherwise>
          <xsl:attribute name="n">
            <xsl:value-of select="$n"/>
          </xsl:attribute>
          <xsl:attribute name="xml:id">
            <xsl:text>l</xsl:text>
            <xsl:value-of select="$n"/>
          </xsl:attribute>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="@part"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>

  <xsl:template match="tei:apostrophe | tei:front/tei:argument | tei:dedicace | tei:examen | tei:preface ">
    <div type="{local-name()}">
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  <xsl:template match="tei:signature">
    <signed>
      <xsl:apply-templates/>
    </signed>
  </xsl:template>
  <xsl:template match="tei:adresse">
    <salute>
      <xsl:apply-templates/>
    </salute>
  </xsl:template>
</xsl:transform>
