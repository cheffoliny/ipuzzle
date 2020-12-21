<script>
	rpc_debug=true;
	
	var my_action = '';
	
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		<input type="hidden" id="status" name="status" value="">
		<input type="hidden" id="id_f" name="id_f" value="{$id_f}">
		<input type="hidden" id="id_r" name="id_r" value="{$id_r}">
		
		<div class="page_caption">{if $id}Редактиране на Обект{else}Нов Обект{/if}</div>
		
		<table class="input">
			<tr class="odd">
				<td width="200">Име на Фирмата:</td>
				<td>
					<select name="id_firm" id="id_firm" onchange="loadXMLDoc( 'refresh' );" />
				</td>
			</tr>
			<tr class="even">
				<td width="200">Име на Региона:</td>
				<td><select name="id_reg" id="id_reg" /></td>
			</tr>
			<tr class="odd">
				<td width="200">Номер на Обекта:</td>
				<td><input type="text" name="num" id="num" class="inp50" onkeypress="return formatDigits(event);" /></td>
			</tr>
			<tr class="even">
				<td width="200">Име на Обекта:</td>
				<td><input type="text" name="name" id="name" /></td>
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

{literal}
	<script>
		loadXMLDoc( 'result' );
		
		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc( 'result' );
				
				my_action = '';
			}
		}
	</script>
{/literal}