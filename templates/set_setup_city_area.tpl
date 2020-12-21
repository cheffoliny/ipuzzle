<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 )">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDCityTrans" name="nIDCityTrans" value="{$nIDCity}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} квартал</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="100">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp200" />
				</td>
			</tr>
			<tr class="even">
				<td width="100">Населено място:</td>
				<td>
					<select name="nIDCity" id="nIDCity" class="select200" />
				</td>
			</tr>
		</table>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2( 'get' );
</script>