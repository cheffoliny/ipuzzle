<script>
	rpc_debug = true;
</script>

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog" onsubmit="loadXMLDoc2( 'save', 3 )">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Ново{/if} назначение на обект</div>
		<br />
		
		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td width="100">Наименование:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp200" />
				</td>
			</tr>
			<tr class="even">
				<td>
					<input type="checkbox" name="nIsSod" id="nIsSod" class="clear ui-nomenclature-checkbox" />&nbsp;СОД
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr class="odd">
				<td>
					<input type="checkbox" name="nIsFo" id="nIsFo" class="clear ui-nomenclature-checkbox" />&nbsp;ФО
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2( 'get' );
</script>
