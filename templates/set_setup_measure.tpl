<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} мерна единица</div>
		<br />

		<table class="input">
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
	loadXMLDoc2('get');
</script>