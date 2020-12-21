{literal}
	<script>
		rpc_debug=true;
		
		function selectNomenclature()
		{
			document.getElementById('nIDScheme').value = 0;
			$('nMode').value = 0;
		}
		
		function selectScheme()
		{
			document.getElementById('nIDNomenclatureType').value = 0;
			document.getElementById('nIDNomenclature').value = 0;
			loadXMLDoc2( 'refresh' );
			$('nMode').value = 1;
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return loadXMLDoc2( 'save', 3 );">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDRequest" name="nIDRequest" value="{$nIDRequest}">
		<input type="hidden" id="nMode" name="nMode" value="0">
		
		<div class="page_caption">{if $nID}Редактиране на Номенклатура{else}Нова Номенклатура{/if}</div>
		<table class="input">
			<tr class="odd"><td colspan="2" style="height: 8px;">&nbsp;</td></tr>
			
			<tr class="even">
				<td>Тип Номенклатура:</td>
				<td><select name="nIDNomenclatureType" id="sNomenclatureType" class="select200" onchange="loadXMLDoc2('refresh'); selectNomenclature();" /></td>
			</tr>
			<tr class="odd">
				<td>Наименование:</td>
				<td><select name="nIDNomenclature" id="sNomenclature" class="select200" onchange="selectNomenclature();" /></td>
			</tr>
			
			<tr class="even">
				<td>Шаблон:</td>
				<td><select name="nIDScheme" id="nIDScheme" class="select200" onchange="selectScheme();" /></td>
			</tr>
			
			<tr class="odd">
				<td>Количество:</td>
				<td><input type="text" name="nCount" id="nCount" class="inp50" /></td>
			</tr>
			
			<tr class="odd"><td colspan="2" style="height: 5px;">&nbsp;</td></tr>
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
	loadXMLDoc2('load');
</script>