<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="block[@name='pages_list']">
        <xsl:call-template name="button1">
            <xsl:with-param name="title">Добавить</xsl:with-param>
            <xsl:with-param name="link">/admin/?op=<xsl:value-of select="/root/common/op"/>&amp;act=edit&amp;id=0</xsl:with-param>
        </xsl:call-template>

        <form method="post" action="">
            <xsl:apply-templates select="list" mode="list" />
            <input type="hidden" name="opcode" value="pages_list" />
        </form>

	</xsl:template>

	<xsl:template match="block[@name='pages_edit']">
        <xsl:apply-templates select="item" mode="edit_item" />
	</xsl:template>

</xsl:stylesheet>