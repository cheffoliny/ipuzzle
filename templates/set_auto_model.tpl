<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2('save', 3)">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} модел</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td>Модел:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp150" />
				</td>
			</tr>
			<tr class="even">
				<td>Марка:</td>
				<td>
					<select name="id_mark" id="id_mark" class="select150" />
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
	loadXMLDoc2('load');
</script>