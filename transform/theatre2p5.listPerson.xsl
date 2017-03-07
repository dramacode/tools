<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.tei-c.org/ns/1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>
  <xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyzeéèêëoôöaâäiîïæœ'"/>
  <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZEÉÈÊËOÔÖAÂÄIÎÏÆŒ'"/>
  <xsl:template match="tei:castList">
    <listPerson>
      <xsl:for-each select="tei:castItem">
        <xsl:choose>
          <xsl:when test="contains(tei:role/@rend, 'female')">
            <person corresp="#{@xml:id}" sex="2"></person>    
          </xsl:when>
          <xsl:when test="contains(tei:role/@rend, 'male') and not(contains(tei:role/@rend, 'female'))">
            <person corresp="#{@xml:id}" sex="1"></person>    
          </xsl:when>
          <xsl:otherwise>
            <person corresp="#{@xml:id}" sex="1"></person>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </listPerson>
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>

</xsl:transform>
