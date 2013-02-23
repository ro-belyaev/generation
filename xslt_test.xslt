<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:msxsl="urn:schemas-microsoft-com:xslt" exclude-result-prefixes="msxsl"
>
    <xsl:output method="xml" indent="yes"/>

    <xsl:template match="/">
      <root>
        <xsl:apply-templates select="//node[@id='all-tests']"/>
      </root>
    </xsl:template>

  <xsl:template match="node">
    <item>
      <xsl:attribute name="id">
        <xsl:value-of select="@id"/>
      </xsl:attribute>
      <content>
        <name>
          <xsl:value-of select="."/>
        </name>
      </content>
      <xsl:apply-templates select="//criterions/criterion[@container=current()/@id]"/>
      <xsl:apply-templates select="//node[@parent-id=current()/@id]"/>
    </item>
  </xsl:template>

  <xsl:template match="criterions/criterion">
    <xsl:variable name="first-id-part" select="./@container"/>
    <xsl:for-each select="condition">
      <xsl:variable name="second-id-part" select="./@value"/>
      <item>
        <xsl:attribute name="id">
          <xsl:value-of select="concat($first-id-part, '_', $second-id-part)"/>
        </xsl:attribute>
        <content>
          <name>
            <xsl:value-of select="description/."/>
          </name>
        </content>
      </item>
    </xsl:for-each>
  </xsl:template>


</xsl:stylesheet>
