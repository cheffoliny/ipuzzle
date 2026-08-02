<script>
	rpc_debug = true;
	rpc_html_debug = true;
</script>

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-assets-report ui-assets-average-report" onSubmit="return false;">
	<div class="page_caption">Активи - Средни Стойности</div>
	
	<center class="ui-nomenclature-filter-wrap">
	
		<table class="search ui-nomenclature-filter ui-assets-report-filter" border="0">
			<tr>
				<td>Група:&nbsp;</td>
				<td>
					<select name="nGroup" id="nGroup" class="select150" />
				</td>
				<td>&nbsp;</td>
				<td class="ui-assets-search-cell" align="right"><button type="button" name="Button" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси </button></td>
			</tr>
		</table>
	
	</center>
	
	<hr />
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'load' );
</script>
