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
                    <xsl:if test="blank = 1"><xsl:attribute name="class">blank</xsl:attribute></xsl:if>
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
                <div class="hint">
                    Памятка по датам:<br />
                    Первый день по Григорианскому календарю - 14 февраля 1918<br/>
                    Последний день по Юлианскому календарю - 31 января 1918<br/>
                    <a href="http://www.direct-time.ru/index.php?id=12#" target="_blank">Калькулятор</a><br /><br />
                    Две даты пишем в формате: Новая дата (старая дата).<br />
                    Одну дату пишем только если это дата по Григорианскому календарю. Старую дату писать только в паре.<br/>
                    Не забываем проверять правильность дат и их соответствие правильному календарю!
                </div>
                Заготовка <input type="checkbox" name="blank" value="1"><xsl:if test="blank = 1"><xsl:attribute
                        name="checked">checked</xsl:attribute></xsl:if> </input>
                <xsl:apply-templates select="posts/item" mode="post_edit"/>
                <input type="submit" value="Сохранить" name="save" />
                <xsl:if test="/root/common/_get/date &gt; 0">
                    <input type="submit" style="margin-left: 20px;" value="Опубликовать сейчас" id="publish_now" name="publish" />
                </xsl:if>
            <input type="hidden" name="do_save" value="1" />
            <input type="hidden" name="op" value="{/root/common/op}" />
            <input type="hidden" name="opcode" value="item_edit" />
            <input type="hidden" name="no_redirect" value="1" />
            </div><br /><br /><br />
        </form>
	</xsl:template>

    <xsl:template match="item" mode="post_edit">
        <div class="post_edit">
            <table>
                <tr><th style="width: 150px"></th><th></th></tr>
                <tr><td><xsl:value-of select="soc_type_title"/><xsl:if test="published = 1"> (<span style="color: red">опубликовано</span>)</xsl:if></td><td><input class="active_check" type="checkbox" name="record[{type}][active]" value="1"><xsl:if test="active = 1"><xsl:attribute
                        name="checked">checked</xsl:attribute></xsl:if> </input></td></tr>
                <tr><td>Текст</td><td><textarea id="{type}_text" name="record[{type}][text]">
                    <!--<xsl:if test="type = 'tw'"><xsl:attribute name="maxlength">140</xsl:attribute></xsl:if>-->
                    <xsl:value-of select="text"/></textarea>
                    <xsl:if test="type = 'tw'">
                        <input type="hidden" id="tw_characters_per_media" value="{/root/common/socials/tw_characters_per_media}" />
                        <input type="hidden" id="tw_short_url_length" value="{/root/common/socials/tw_short_url_length}" />
                        <div id="tw_count"></div>
                        <div class="hint">
                        Ссылка для twitter кодируется в укороченный вариант <a href="http://tiny.cc/" target="_blank">здесь</a> и вставляется в текст.<br />
                        При копировании текста в поле следите, чтобы количество символов не превышало <b>140</b>, иначе твиттер не примет сообщение!<br />
                        Если предполагается <b>картинка</b>, то количество символов не должно превышать <b><xsl:value-of
                                select="140-/root/common/socials/tw_characters_per_media"/>!</b><br />
                        Если вставляется короткая <b>ссылка</b> в текст, то количество символов не должно превышать <b><xsl:value-of
                                select="140-/root/common/socials/tw_short_url_length + 21"/></b>,<br />а с картинкой соответственно <b><xsl:value-of
                                select="140 + 21 -/root/common/socials/tw_characters_per_media - /root/common/socials/tw_short_url_length"/></b>!<br />
                    </div></xsl:if>
                </td></tr>
                <xsl:choose>
                    <xsl:when test="type != 'tw'">
                        <tr><td>Ссылка</td><td><input type="text" name="record[{type}][url]" value="{url}" /></td></tr>
                        <tr><td>Теги</td><td><input type="text" name="record[{type}][tags]" value="{tags}" /></td></tr>
                    </xsl:when>
                    <xsl:otherwise>
                        <input type="hidden" name="record[{type}][url]" value="" />
                        <input type="hidden" name="record[{type}][tags]" value="" />
                    </xsl:otherwise>
                </xsl:choose>
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

                <a href="{fb_login_url}">Update facebook access token</a><br /><br />
                <a href="/admin/?op=posts&amp;act=tokens&amp;type=tw">Update twitter configuration</a><br /><br />
                <!--<a href="{fb_login_url}">Get OK.ru access token</a>-->

            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
    
</xsl:stylesheet>