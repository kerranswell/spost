<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="block[@name='posts_list']">
        <xsl:call-template name="button1">
            <xsl:with-param name="title">Добавить</xsl:with-param>
            <xsl:with-param name="link">/admin/?op=<xsl:value-of select="/root/common/op"/>&amp;act=edit&amp;date=0</xsl:with-param>
        </xsl:call-template>
<br/>
        <div>
            <form action="" method="post">
        Фильтр:
        <br/>
        От <input type="text" name="date_from" class="datetimepick" value="{date_from}"/>
        <br />
        До <input type="text" name="date_to" class="datetimepick" value="{date_to}"/><br />
            <input type="submit" value="Фильтр" name="go" class="filter_go" />
            <input type="hidden" value="datefilter" name="opcode" />
            </form>
        </div>

        <xsl:apply-templates select="list" mode="posts_list" />

	</xsl:template>

    <xsl:template match="list" mode="posts_list">
        <table border="0" class="tree_node_list">
            <xsl:for-each select="item">
                <tr>
                    <td><xsl:value-of select="date_title"/></td>
                    <td><xsl:value-of select="text"/></td>
                    <td><xsl:value-of select="types"/></td>
                    <td><xsl:if test="image != ''"><a href="{image}" target="_blank"><img src="{image_th}" /></a></xsl:if></td>
                    <td><a href="/admin/?op={/root/common/op}&amp;act=edit&amp;date={date}"><img src="/admin/static/img/edit.png" /></a></td>
                    <td><a class="delete" href="/admin/?op={/root/common/op}&amp;act=delete&amp;date={date}"><img src="/admin/static/img/del.png" /></a></td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>

	<xsl:template match="block[@name='posts_edit']">
        <form method="post" class="edit_item_form" action="" enctype="multipart/form-data">
            <div class="posts_edit">
            Дата: <input type="text" value="{date}" name="date" /><br />
                <xsl:apply-templates select="posts/item" mode="post_edit"/>
                <input type="submit" value="Сохранить" name="save" />
            <input type="hidden" name="do_save" value="1" />
            <input type="hidden" name="op" value="{/root/common/op}" />
            <input type="hidden" name="opcode" value="item_edit" />
            <input type="hidden" name="no_redirect" value="1" />
            </div>
        </form>
	</xsl:template>

    <xsl:template match="item" mode="post_edit">
        <div class="post_edit">
            <table>
                <tr><th style="width: 150px"></th><th></th></tr>
                <tr><td><xsl:value-of select="soc_type_title"/></td><td><input class="active_check" type="checkbox" name="record[{type}][active]" value="1"><xsl:if test="active = 1"><xsl:attribute
                        name="checked">checked</xsl:attribute></xsl:if> </input></td></tr>
                <tr><td>Текст</td><td><textarea name="record[{type}][text]">
                    <xsl:if test="type = 'tw'"><xsl:attribute name="maxlength">140</xsl:attribute></xsl:if>
                    <xsl:value-of select="text"/></textarea></td></tr>
                <tr><td>Url</td><td><input type="text" name="record[{type}][url]" value="{url}" /></td></tr>
                <tr><td>Картинка</td><td><input type="file" name="record[{type}][image]" />
                    <xsl:if test="image != 0"><br /><input type="checkbox" name="{type}_image_delete" value="1" /> Удалить<br /><a href="{image}" target="_blank"><img src="{image_th}" /></a></xsl:if>
                </td></tr>
            </table>
        </div>
    </xsl:template>


    <xsl:template match="block[@name='tokens']">
        <xsl:choose>
            <xsl:when test="status = 'ok'">Успешно!</xsl:when>
            <xsl:otherwise>

                <a href="{fb_login_url}">Update facebook access token</a>

            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
    
</xsl:stylesheet>