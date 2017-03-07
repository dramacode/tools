<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
  <xsl:output method="html" encoding="UTF-8" indent="yes" doctype-public="-//W3C//DTD XHTML//EN" doctype-system="http://www.w3.org/TR/2001/REC-xhtml11-20010531"/>
  <xsl:template match="/">
    <html>
      <head><title>Title</title></head>
      <body>
        <xsl:for-each select="//tei:role">
          <xsl:variable name="id">
            <xsl:value-of select="@xml:id"/>
          </xsl:variable>
          <xsl:value-of select="count(//tei:p[ancestor::tei:sp[@who=@xml:id]])"/>
          <br/>
        </xsl:for-each>
      </body>
    </html>
  </xsl:template>
</xsl:transform>
