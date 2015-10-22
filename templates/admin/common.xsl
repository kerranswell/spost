<?xml version="1.0" encoding="UTF-8" ?> 
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


    <xsl:template name="button1">
        <xsl:param name="title"/>
        <xsl:param name="link"/>
        <a href="{$link}"><xsl:value-of select="$title"/></a>
    </xsl:template>

    <!-- EDIT -->

    <xsl:template match="field" mode="table_row">
        <tr valign="top"><td align="right">
            <xsl:value-of select="./@title"/></td><td><xsl:apply-templates select="." mode="input"/>
            <xsl:if test="./@error != ''"><br /><span class="error"><xsl:value-of select="./@error"/></span></xsl:if>
        </td></tr>
    </xsl:template>


    <xsl:template match="field[@showtype='string']" mode="input">
        <input type="text" value="{.}" name="record[{./@name}]" />
    </xsl:template>

    <xsl:template match="field[@showtype='image']" mode="input">
        <input type="file" value="" name="record[{./@name}]" />
        <xsl:if test=". != 0"><br /><input type="checkbox" name="{./@name}_delete" value="1" /> Удалить<br /><img src="{.}" /></xsl:if>
    </xsl:template>


    <xsl:template match="field[@showtype='editor']" mode="input">
        <textarea class="ckeditor" name="record[{./@name}]"><xsl:value-of select="."/></textarea>
    </xsl:template>


    <xsl:template match="field[@showtype='checkbox']" mode="input">
        <input type="checkbox" name="record[{./@name}]" value="1"><xsl:if test=". = 1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
    </xsl:template>

    <xsl:template match="field[@showtype='none']" mode="table_row" />


    <xsl:template match="item" mode="edit_item">
        <form method="post" class="edit_item_form" action="" enctype="multipart/form-data">
            <table border="0" width="80%">
                <tr>
                <th width="200px"></th>
                <th></th>
                </tr>
                <xsl:apply-templates select="field" mode="table_row"/>
                <tr><td></td><td><input type="submit" value="Сохранить" name="save" /></td></tr>
            </table>
            <input type="hidden" name="do_save" value="1" />
            <input type="hidden" name="op" value="{/root/common/op}" />
            <input type="hidden" name="opcode" value="item_edit" />
            <input type="hidden" name="no_redirect" value="1" />
        </form>
    </xsl:template>

    <!-- LIST -->

    <xsl:template match="item" mode="list_item">
        <tr item_id="{field[@name='id']}">
            <xsl:attribute name="class">
                <xsl:if test="field[@name='status'] = 0">unactive</xsl:if>
            </xsl:attribute>

            <td><div class="drag"></div></td>
            <xsl:apply-templates select="field" mode="list_field" />
            <td><a href="/admin/?op={/root/common/op}&amp;act=edit&amp;id={field[@name='id']}"><img src="/admin/static/img/edit.png" /></a></td>
            <td><a class="delete" href="/admin/?op={/root/common/op}&amp;act=delete&amp;id={field[@name='id']}"><img src="/admin/static/img/del.png" /></a></td>
        </tr>
    </xsl:template>

    <xsl:template match="field[@showtype='none']" mode="list_field" />

    <xsl:template match="field[@showtype='label']" mode="list_field">
        <td><xsl:value-of select="." /></td>
    </xsl:template>

    <xsl:template match="field[@showtype='link_children']" mode="list_field">
        <td><a href="/admin/?op={/root/common/op}&amp;pid={../field[@name='id']}"><xsl:value-of select="." /></a></td>
    </xsl:template>

    <xsl:template match="field[@showtype!='none']" mode="list_header">
        <th><xsl:value-of select="./@title" /></th>
    </xsl:template>

    <xsl:template match="field[@showtype='none']" mode="list_header" />

    <xsl:template match="list" mode="list">
        <table border="0" class="tree_node_list" index_start="0" table="{/root/common/op}" sortable="1">
            <tr class="table_header">
                <th></th>
                <xsl:apply-templates select="../fields/field" mode="list_header" />
                <th></th>
                <th></th>
            </tr>
            <xsl:apply-templates select="item" mode="list_item"/>
        </table>
    </xsl:template>

    <!-- PATH -->

    <xsl:template match="block[@name='path']">
        <ul class="path">
            <xsl:for-each select="item">
                <li>
                    <xsl:choose>
                        <xsl:when test="position() = count(../item) and /root/mod_params/act = 'list'"><xsl:value-of select="title"/></xsl:when>
                        <xsl:otherwise><a href="/admin/?op={/root/common/op}&amp;pid={id}"><xsl:value-of select="title"/></a></xsl:otherwise>
                    </xsl:choose>
                </li>
                <xsl:if test="position() &lt; count(../item)">
                    <li>&#187;</li>
                </xsl:if>
            </xsl:for-each>
        </ul>
    </xsl:template>


</xsl:stylesheet>
