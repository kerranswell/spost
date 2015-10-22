<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:template match="block[@name='nav']">
        <ul class="main_menu">
            <xsl:for-each select="item">
                <li><xsl:if test="/root/common/op = ./op"><xsl:attribute name="class">active</xsl:attribute></xsl:if>
                    <a href="{link}"><xsl:value-of select="title"/></a></li>
            </xsl:for-each>
        </ul>
    </xsl:template>


</xsl:stylesheet>