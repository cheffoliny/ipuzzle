<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 )">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} причина за профилактика</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="100">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp200" />
				</td>
			</tr>
			<tr class="even">
				<td colspan="2">
					<input type="checkbox" name="nFromTechSignals" id="nFromTechSignals" class="clear" />&nbsp;Използва се от технически справки
				</td>
			</tr>
		</table>
		
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