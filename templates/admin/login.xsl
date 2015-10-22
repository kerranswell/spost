<?xml version="1.0" encoding="UTF-8" ?> 
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:import href="layout.xsl"/> 
	
	<xsl:template match="block[@name='login']">
		<div class="center">
			<form action="" method="post" class="form-horizontal">
				
				<fieldset>
					<input type="hidden" name="opcode" value="login"/>
					
					<legend>Вход в систему</legend>
					
					<div class="control-group">
						<label class="control-label" for="username">Username:</label>
						<div class="controls">
							<input type="text" name="username" id="username" placeholder="username"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="password">Password:</label>
						<div class="controls">
							<input type="password" name="password" placeholder="password"/>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" value="Вход" class="btn btn-primary">Вход</button>
						</div>
					</div>
				</fieldset>			
			</form>
		</div>
	</xsl:template>

	<xsl:template match="block[@name='logout']">
		<center>
			<div class="logout">
				<div class="title">Выход из системы</div>
				<div class="form">
					<form method="post" action="">
						<input type="hidden" name="opcode" value="logout"/>
						<input type="submit" class="button" value="Logout" />
					</form>
				</div>
			</div>
		</center>
	</xsl:template>

</xsl:stylesheet>