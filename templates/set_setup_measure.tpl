<script>
	rpc_debug = true;
</script>

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} мерна единица</div>
		<br />

		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td width="100">Код:</td>
				<td>
					<input type="text" name="sCode" id="sCode" class="inp50" />
				</td>
			</tr>
			<tr class="even">
				<td width="100">Единица:</td>
				<td>
					<input type="text" name="sDescription" id="sDescription" class="inp200" />
				</td>
			</tr>
		</table>
		
		<br />
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
	loadXMLDoc2('get');
</script>
