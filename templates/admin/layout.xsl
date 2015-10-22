<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.1">
	<xsl:import href="common.xsl"/>
	<xsl:import href="navbar.xsl"/>
	
	<xsl:output method="html" indent="yes"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" encoding="utf-8"/>
	
	
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
			<head>
				<title>Admin</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
				<!--<link rel="stylesheet" type="text/css" href="/admin/static/css/bootstrap.css" />-->
				<!--<link rel="stylesheet" type="text/css" href="/admin/static/css/bootstrap-toggle-buttons.css" />-->
				<link rel="stylesheet" type="text/css" href="/admin/static/css/admin.css" />
				<!--<link rel="stylesheet" type="text/css" href="/admin/static/css/custom/photolabel.css" />-->
				<link rel="stylesheet" type="text/css" href="/admin/static/jquery-ui/themes/base/jquery-ui.css" />

				<script src="/static/js/jquery.js"/>
				<script src="/admin/static/js/jquery-ui.js"/>
				<!--<script src="/admin/static/js/jquery.base64.js"/>-->
				<!--<script src="/admin/static/js/bootstrap.js"/>-->
				<!--<script src="/admin/static/js/jquery.toggle.buttons.js"/>-->
				<!--<script src="/admin/static/js/custom/verification.js"/>-->
                <script xmlns="" type="text/javascript" src="/admin/static/ckeditor/ckeditor.js"></script>
				<script src="/admin/static/js/custom/admin.js"/>

				<xsl:apply-templates select="/node()/head"/>
			</head>
			<body>

                <table border="1" class="main_table" width="100%" height="100%">
                  <tr valign="top"><td width="200px">
                      <xsl:apply-templates select="/node()/block[@align='left']"/>
                </td><td>
                      <xsl:apply-templates select="/node()/block[@align='center']"/>
                  </td></tr>
                </table>
			</body>
		</html>
	</xsl:template>
	

</xsl:stylesheet>
