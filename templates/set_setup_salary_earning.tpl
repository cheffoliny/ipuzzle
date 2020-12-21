<script>
	rpc_debug=true;
		
	var my_action = '';
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Редактиране на Наработка{else}Нов Запис{/if}</div>

		<table class="input">
			<tr class="even">
				<td width="120">Код на Наработката:</td>
				<td style="padding-left: 15px;">
					<input type="text" name="code" style="width: 102px;" id="code" />
				</td>
			</tr>
			<tr class="odd">
				<td>Наименование:</td>
				<td style="padding-left: 15px;">
					<input type="text" name="name" id="name" style="width: 240px;" />
				</td>
			</tr>
			<tr class="even">
				<td>Мерна Единица:</td>
				<td style="padding-left: 15px;">
					<select style="width: 105px;" name="measure" id="measure"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>Източник:</td>
				<td style="padding-left: 15px;">
					<select style="width: 115px;" name="source" id="source"></select>
				</td>
			</tr>
			<tr class="even">
				<td>Използва се при отпуски:</td>
				<td style="padding-left: 15px;">
					<select id="sLeaveType" name="sLeaveType" style="width: 115px;">
						<option value="none">---</option>
						<option value="due">Платен</option>
						<option value="unpaid">Неплатен</option>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td>Обезщетение:</td>
				<td style="padding-left: 10px;">
					<input type="checkbox" id="nIsCompensation" name="nIsCompensation" class="clear" />
				</td>
			</tr>
			<tr class="even">
				<td>Болнични:</td>
				<td style="padding-left: 10px;">
					<input type="checkbox" id="nIsHospital" name="nIsHospital" class="clear" />
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