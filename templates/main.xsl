<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <html>
            <head>
                <title>Календарь Истории России</title>
                <meta name="description" content="Каждый день о важных событиях из истории России" />
                <meta property="og:title" content="Календарь Истории России" />
                <meta property="og:description" content="Каждый день о важных событиях из истории России" />
                <meta property="og:image" content="/static/i/og_image.jpg" />
                <link rel="stylesheet" href="/static/css/style.css" type="text/css" media="all" />
                <script src="http://vk.com/js/api/openapi.js" type="text/javascript"></script>
            </head>

            <body>
                <div id="wrapper">
                    <div class="header">
                        <div class="title"><h1>Календарь Истории России</h1></div>
                    </div>

                    <div class="main">
                        <div class="content">
                            <p>Сегодня <xsl:value-of select="//block[@name='main']/date"/> года.</p><br />
                            <p>Узнайте, чем примечателен этот день в истории России.</p><br />
                            <p>Подписывайтесь на нас в социальных сетях:</p><br /><br />
                            <div class="s_icon"><a href="http://vk.com/dailyhistory" target="_blank"><img src="/static/i/vk_icon.png" /></a></div>
                            <div class="s_icon"><a href="http://facebook.com/dailyhistory" target="_blank"><img src="/static/i/fb_icon.png" /></a></div>
                            <div class="s_icon"><a href="http://twitter.com/dailyhistoryrus" target="_blank"><img src="/static/i/tw_icon.png" /></a></div>
                            <div class="s_icon"><a href="http://instagram.com/dailyhistoryrus" target="_blank"><img src="/static/i/in_icon.png" /></a></div>
                            <div class="s_icon"><a href="http://ok.ru/group/54998164242463" target="_blank"><img src="/static/i/ok_icon.png" /></a></div>

                        </div>
                        <div class="sidebar">
                            <div id="vk_groups"></div>
                            <script type="text/javascript">
                                VK.Widgets.Group("vk_groups", {mode: 2, width: "300", height: "400"}, 105226635);
                                <!-- 105226635 -->
                            </script>
                        </div>
                    </div>
                </div>
            </body>

        </html>
    </xsl:template>

</xsl:stylesheet>