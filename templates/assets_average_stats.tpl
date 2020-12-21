<script>
	rpc_debug = true;
</script>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<div class="page_caption">Активи - Средни Стойности</div>
	
	<br />
	
	<center>
	
		<table class="search" border="0">
			<tr>
				<td>Група:&nbsp;</td>
				<td>
					<select name="nGroup" id="nGroup" class="select150" />
				</td>
				<td>&nbsp;</td>
				<td style="padding-left: 50px" align="right"><button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif"> Търси </button></td>
			</tr>
		</table>
	
	</center>
	
	<hr />
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'load' );
</script>