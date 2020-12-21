{literal}
	<script>
		rpc_debug = true;

		rpc_on_exit = function()
		{
			$('uplaoded_file').value="";
		}
		
		InitSuggestForm = function  ()
		{			
			for(var i = 0; i < suggest_elements.length; i++) 
			{
//				if( suggest_elements[i]['id'] == 'firm' ) 
//				{
//					suggest_elements[i]['suggest'].setSelectionListener( onSuggestFirm );
//				}
//				if( suggest_elements[i]['id'] == 'region' ) 
//				{
//					suggest_elements[i]['suggest'].setSelectionListener( onSuggestRegion );
//				}
				if( suggest_elements[i]['id'] == 'region_object' )
				{
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
				}
			}
		}
		
//		function onSuggestFirm ( aParams ) 
//		{
//			$('id_firm').value = aParams.KEY;
//			$('region').value = '';
//			$('region_object').value = '';
//		}
//		
//		function onSuggestRegion ( aParams ) 
//		{
//			$('id_region').value = aParams.KEY;
//			$('region_object').value = '';
//		}
		
		function onSuggestObject ( aParams ) 
		{
			$('id_object').value = aParams.KEY;
		}
		
		function onFirmChange()
		{
			$('id_object').value = 0;
			$('region_object').value = '';
			loadXMLDoc( 'fillRegions' );
		}
		
		function onRegionChange()
		{
			$('id_object').value = 0;
			$('region_object').value = '';
		}
		
		function onRegionObjectChange()
		{
			$('id_object').value = 0;
		}
		
		function deleteSalary( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('id').value = id;
				loadXMLDoc('delete_salary');
			}
		}
		
		function ImportSalary()
		{
			if( $("import_type").value == 'gsm')
				dialogImportSlaryGSM();
			else
				dialogImportSlary();
		}
		
		function personnel( id )
		{
			dialogPerson( id );
		}
		
		function openFixSalary() {
			dialogFixSalary();
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return loadXMLDoc('result')">
	<input type="hidden" id="id_object" name="id_object" valur="0">
	<input type="hidden" id="uplaoded_file" name="uplaoded_file_name" valur="">
	<input type="hidden" id="uplaoded_file" name="uplaoded_file_type" valur="">
	<input type="hidden" id="id" name="id" value="0">
	<input type="hidden" id="year_month" name="year_month" valur="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Администрация - РАБОТНИ ЗАПЛАТИ (Подробна)</td>
			<td class="buttons">
				<table>
					<tr>
						<!--<td style="width:100px;">
							<button style="width:60px;" onClick="openFixSalary()"><b>+ЩАТ</b></button>
						</td> --> 
						<td style="font-size:12px">
							Импорт на
						</td>
						<td>
							<select name="import_type" id="import_type" class="select150">
								<option value='salary'/>Работни заплати
								<option value='gsm'/>Фактура МТЕЛ
							</select>
						</td>
						<td>
							<button onclick="ImportSalary()"><img src="images/plus.gif">От Файл</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Тип</td>
				<td align="left">
					<select id="type" name="type" class="select200">
						<option value="1">Служители от</option>
						<option value="2">За сметка на</option>
					</select>&nbsp;&nbsp;
				</td>
				<td align="right">Фирма</td>
				<td align="left">
					<select name="firm" id="firm" class="select200" onchange="onFirmChange()" />&nbsp;&nbsp;
				</td>
				<td align="center">
					Год
					<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td align="right">Регион</td>
				<td align="left">
					<select name="region" id="region" class="select200" onchange="onRegionChange()" />
				</td>
				<td align="right">Обект</td>
				<td align="left">
					<input name="region_object" id="region_object" type="text" class="inp200" suggest="suggest" queryType="region_object" queryParams="firm;region" onchange="onRegionObjectChange()" onpast="onRegionObjectChange()"/>
				</td>
				<td align="center"><button type="submit" name="Button"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  </table>
	</center>
	
	<hr>
	
	<div id="result"></div>
	
</form>

<script>
	loadXMLDoc( 'fillFirms' );
</script>