<script>
	rpc_debug=true;
	
	var my_action = '';
</script>

<div class="content">
	<form name="form1" id="form1" action="" method="POST" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 3 );">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Редактиране на фирма{else}Нова фирма{/if}</div>
		<table class="input">
			<tr class="odd">
				<td width="220">Код на Фирмата:</td>
				<td><input id="code" name="code" type="text" class="inp50" onkeypress="return formatDigits(event);" /></td>
			</tr>
			<tr class="even">
				<td>Наименование:</td>
				<td><input id="name" name="name" type="text" class="inp200" /></td>
			</tr>
			<tr class="odd">
				<td>МОЛ:</td>
				<td><input id="mol" name="mol" type="text" class="inp200" /></td>
			</tr>
		</table>
		
		<fieldset>
			<legend>Юридическо Лице:</legend><br />
			<table class="input">
				<tr class="odd">
					<td width="220">Наименование:</td>
					<td><input id="jur_name" name="jur_name" type="text" class="inp200" /></td>
				</tr>
				<tr class="even">
					<td>Адрес по Данъчна Регистрация:</td>
					<td><input id="address" name="address" type="text" class="inp200" /></td>
				</tr>
				<tr class="odd">
					<td>Идентификационен Номер:</td>
					<td><input id="idn" name="idn" type="text" class="inp200" /></td>
				</tr>
				<tr class="even">
					<td>Иден Номер по ДДС:</td>
					<td><input id="idn_dds" name="idn_dds" type="text" class="inp200" /></td>
				</tr>
				<tr class="odd">
					<td>МОЛ:</td>
					<td><input id="jur_mol" name="jur_mol" type="text" class="inp200"/></td>
				</tr>
			</table><br />
		</fieldset>
		
		<fieldset>
			<legend>ДДС:</legend>
			<table class="input">
				<tr class="odd">
					<td	width="220">Фирма:</td>
					<td>
						<select id="nIDFirmDDS" name="nIDFirmDDS" class="select200" onchange="loadXMLDoc( 'getoffices' );" />
					</td>
				</tr>
				<tr class="even">
					<td	width="220">Регион:</td>
					<td>
						<select id="nIDOfficeDDS" name="nIDOfficeDDS" class="select200" />
					</td>
				</tr>
			</table>
		</fieldset>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="225">Банкова сметка:</td>
				<td>
					<select id="nIDBankAccountDefault" name="nIDBankAccountDefault" class="select200" />
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