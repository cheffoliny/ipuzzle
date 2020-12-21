<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Редактиране на актив{else}Нов актив{/if}</div>
		<table class="input">
			<tr class="odd" style="height: 10px;"><td colspan="2"></td></tr>
			<tr class="even">
				<td width="120">Код на актива:</td>
				<td><input type="text" name="code" id="code" style="width: 120px;" /></td>
			</tr>
			<tr class="even">
				<td>Наименование:</td>
				<td><input type="text" name="name" id="name" style="width: 263px;" /></td>
			</tr>
			<tr class="odd" style="height: 10px;"><td colspan="2"></td></tr>
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
