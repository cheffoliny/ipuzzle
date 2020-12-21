{literal}
	<script>
		rpc_debug = true;
		
		function switchStatus()
		{
			var currentState = $('sStatus').value;
			
			switch( currentState )
			{
				case "attached":
					document.getElementById( "attached" ).style.display = "block";
					document.getElementById( "entered" ).style.display = "none";
					break;
				case "entered":
					document.getElementById( "attached" ).style.display = "none";
					document.getElementById( "entered" ).style.display = "block";
					break;
				case "wasted":
					document.getElementById( "attached" ).style.display = "none";
					document.getElementById( "entered" ).style.display = "none";
					break;
				case "out":
					document.getElementById( "attached" ).style.display = "none";
					document.getElementById( "entered" ).style.display = "none";
					break;
				
				default:
					break;
			}
		}
		
		function openAsset( id )
		{
			dialogAssetInfo( id );
		}
		
		function gotoNomenclatures( id )
		{
			$("nIDCustomGroup").value = id;
			$("sResultType").value = "nomenclatures";
			loadXMLDoc2( "result" );
		}
		
		function gotoAssets( id )
		{
			$("nIDCustomNomenclature").value = id;
			$("sResultType").value = "detailed";
			loadXMLDoc2( "result" );
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="nIDCustomGroup" name="nIDCustomGroup" value="0" />
	<input type="hidden" id="nIDCustomNomenclature" name="nIDCustomNomenclature" value="0" />
	
	<div class="page_caption">Активи - Инвентаризация</div>
	
	<br />
	
	<center>
	
		<table class="search" border="0" width="850">
			<tr>
				<td align="left" width="130">Тип на Справката:&nbsp;</td>
				<td align="left" width="235">
					<select name="sResultType" id="sResultType" class="select150">
						<option value="detailed">Подробна</option>
						<option value="general">Обобщена</option>
						<option value="groups">По Групи</option>
						<option value="subgroups">По Подгрупи</option>
						<option value="nomenclatures">По Номенклатури</option>
					</select>
				</td>
				
				<td>&nbsp;</td>
				
				<td align="left" width="90">Статус:&nbsp;</td>
				<td align="left">
					<select name="sStatus" id="sStatus" class="select200" onchange="switchStatus();">
						<option value="attached">Въведени</option>
						<option value="entered">Придобити</option>
						<option value="wasted">Бракувани</option>
						<option value="out">Изтекъл срок на амортизация</option>
					</select>
				</td>
				
				<td style="padding-left: 50px" align="right"><button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif"> Търси </button></td>
			</tr>
		</table>
		
		<div id="attached" style="display: block;">
			<table class="search" border="0" width="850">
				<tr>
					<td align="left" width="130">Фирма:&nbsp;</td>
					<td align="left" width="230">
						<select id="nIDFirm" name="nIDFirm" class="select200" onchange="loadXMLDoc2( 'refreshOffices' );" />
					</td>
					
					<td>&nbsp;</td>
					
					<td align="left" width="90">Регион:&nbsp;</td>
					<td align="left">
						<select id="nIDOffice" name="nIDOffice" class="select200" onchange="loadXMLDoc2( 'refreshPersons' );" />
					</td>
				</tr>
				<tr>
					<td align="left" width="130">Служител:&nbsp;</td>
					<td align="left" width="230">
						<select id="nIDPerson" name="nIDPerson" class="select200" />
					</td>
					
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>
		<div id="entered" style="display: none;">
			<table class="search" border="0" width="850">
				<tr>
					<td align="left" width="130">Склад:&nbsp;</td>
					<td align="left" width="720">
						<select id="nIDStoragehouse" name="nIDStoragehouse" class="select200" />
					</td>
				</tr>
			</table>
		</div>
		<table class="search" border="0" width="850">
			<tr>
				<td align="left" width="130">Група:&nbsp;</td>
				<td align="left" width="230">
					<select id="nIDGroup" name="nIDGroup" class="select200" onchange="loadXMLDoc2( 'refreshNomenclatures' );" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="left" width="90">Номенклатура:&nbsp;</td>
				<td align="left">
					<select id="nIDNomenclature" name="nIDNomenclature" class="select200" />
				</td>
			</tr>
		</table>
		
	</center>
	
	<hr />
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2( "load" );
</script>