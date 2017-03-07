<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="tei:privilege|tei:acheveImprime|tei:imprimeur|tei:performance|tei:docTitle|tei:docImprint|tei:docAuthor|tei:docDate|tei:titlePart|tei:premiere|tei:set|tei:printer">
    <div>
      <xsl:attribute name="type"><xsl:value-of select="name(.)"/></xsl:attribute>
     
      <xsl:apply-templates select="node()|@*"/>
    </div>
  </xsl:template>
  <xsl:template match="tei:profileDesc/tei:textClass/tei:keywords">
 <xsl:copy>
   <xsl:apply-templates select="node()|@*"/>
    
      
        <xsl:for-each select="//tei:sourceDesc/*">
          <term type="{name(.)}">
            <xsl:value-of select="."/>
          </term>
        </xsl:for-each>
   <xsl:for-each select="//tei:author/@*">
     <term type="{name(.)}">
       <xsl:value-of select="."/>
     </term>
   </xsl:for-each>
    
 </xsl:copy> 
  </xsl:template>
<xsl:template match="tei:sourceDesc">
  <sourceDesc><bibl></bibl></sourceDesc>
</xsl:template>
  <xsl:template match="tei:authority">
    <pubPlace>
      <xsl:apply-templates select="node()|@*"/>
    </pubPlace>
  </xsl:template>
  <xsl:template match="tei:author">
    <author><xsl:value-of select="."/></author>
  </xsl:template>
  <xsl:template match="tei:date"><xsl:copy><xsl:apply-templates select="node()|@*"/><xsl:value-of select="substring(@when, 1, 4)"/></xsl:copy></xsl:template>
</xsl:transform>
