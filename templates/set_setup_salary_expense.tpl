<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="my_action = 'save';return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Редактиране на Удръжка{else}Нов Запис{/if}</div>

		<table class="input">
			<tr class="even">
				<td width="130">Код на Отчислението:</td>
				<td><input type="text" name="code" style="width: 102px;" id="code" /></td>
			</tr>
			<tr class="odd">
				<td>Наименование:</td>
				<td><input type="text" name="name" id="name" style="width: 233px;" /></td>
			</tr>
			<tr class="even">
				<td>Мерна Единица:</td>
				<td>
					<select style="width: 105px;" name="measure" id="measure"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>Източник:</td>
				<td>
					<select style="width: 115px;" name="source" id="source"></select>
				</td>
			</tr>
			<tr><td colspan="2" style="height: 5px;"></td></tr>			
		</table>

		<table class="input">
			<tr class="odd">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
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

{literal}
	<script>
		loadXMLDoc('result');
		
		
		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
	</script>
{/literal}