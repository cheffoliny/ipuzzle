{literal}
	<script>
		rpc_debug = true;
		
		InitSuggestForm = function()
		{
			for( var i = 0; i < suggest_elements.length; i++ )
			{
				if( suggest_elements[i]['id'] == 'sStoragehouse' )
				{
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestStoragehouse );
				}
			}
		}
		
		function onSuggestStoragehouse( aParams )
		{
			$('nIDStoragehouse').value = aParams.KEY;
		}
		
		function onInit()
		{
			nullStorage();
			loadXMLDoc2( 'load' );
		}
		
		function nullStorage()
		{
			document.getElementById( 'sStoragehouse' ).value = '';
			document.getElementById( 'nIDStoragehouse' ).value = 0;
			
			var sStorage = '';
			switch( $('sStorageType').value )
			{
				case 'storagehouse' : 	sStorage = 'Склад'; 	break;
				case 'person' : 		sStorage = 'Служител'; 	break;
				case 'object' : 		sStorage = 'Обект'; 	break;
			}
			
			if( $('sStorageType').value == 'object' )
			{
				document.getElementById( 'type_caption_obj' ).style.display = "block";
				document.getElementById( 'type_select_obj' ).style.display = "block";
			}
			else
			{
				document.getElementById( 'type_caption_obj' ).style.display = "none";
				document.getElementById( 'type_select_obj' ).style.display = "none";
			}
			
			
			if( $('sStorageType').value == 'storagehouse' )
			{
				document.getElementById( 'type_caption' ).style.display = "block";
				document.getElementById( 'type_select' ).style.display = "block";
			}
			else
			{
				document.getElementById( 'type_caption' ).style.display = "none";
				document.getElementById( 'type_select' ).style.display = "none";
			}
			
			document.getElementById( 'storagetype' ).innerHTML = sStorage;
		}
		
		function changesHandler()
		{
			var myValue = document.getElementById( 'sStoragehouse' ).value
			
			if( myValue == '' )document.getElementById( 'nIDStoragehouse' ).value = 0;
		}
		
		function openFilter( type )
		{
			var id;
			if( type == 1 )
			{
				dialogStatesFilter( 0 );
			}
			else
			{
				id = $('schemes').value;
				if( id != 0 )
				{
					dialogStatesFilter( id );
				}
			}
		}
		
		function deleteFilter( schemes )
		{
			if( schemes.value > 0 )
				if( confirm( 'Наистина ли желаете да премахнeте филтърът?' ) )
				{
					loadXMLDoc( 'deleteFilter', 6 );
				}
		}
		
		function openObject( id )
		{
			var sParams = new String();
			
			if( id )
			{
				var aBothIDs = id.split( "," );
				
				if( aBothIDs[1] )
				{
					sParams = 'nID=' + aBothIDs[1];
					
					dialogObjectInfo( sParams );
				}
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-monitor-report ui-states-report" onSubmit="return false;">
	<input type="hidden" name="nIDStoragehouse" id="nIDStoragehouse" value="0">
	
	<table class="page_data ui-monitor-heading ui-monitor-toolbar">
		<tr>
			<td class="page_name">Наличности</td>
			
			<td align="right">
				Филтри:
			</td>
			<td style="width:200px;">
				<select name="schemes" id="schemes" onchange="loadXMLDoc2('load');"></select>
			</td> 
			
			<td>
				<button type="button" class="ui-monitor-icon-button" title="Нов филтър" name="Button5" onClick="openFilter(1);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span></button>&nbsp;
				<button type="button" class="ui-monitor-icon-button" name="Button4" title="Редактиране на филтър" onClick="openFilter(2);"><span class="ui-icon ui-icon-edit" aria-hidden="true"></span></button>&nbsp;
				<button type="button" class="ui-monitor-icon-button ui-monitor-danger" name="Button3" title="Премахване на филтър" onClick="deleteFilter(schemes);"><span class="ui-icon ui-icon-delete" aria-hidden="true"></span></button>
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search ui-monitor-filter ui-states-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select name="nIDFirm" id="nIDFirm" class="select200" onchange="loadXMLDoc2( 'loadOffices' )" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right">Регион</td>
				<td>
					<select name="nIDOffice" id="nIDOffice" class="select200" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right">Тип</td>
				<td>
					<select name="sStorageType" id="sStorageType" class="select150" onchange="nullStorage();">
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="object">Обект</option>
					</select>
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right"><div id="storagetype">Склад</div></td>
				<td>
					<input name="sStoragehouse" id="sStoragehouse" class="inp200" suggest="suggest" queryType="statesStorage" queryParams="sStorageType;nIDOffice;nIDFirm" onchange="changesHandler();" />
				</td>
			</tr>
			
			<tr>
				<td align="right">Тип Номенклатура</td>
				<td>
					<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="select200" onchange="loadXMLDoc2( 'loadNomenclatures' )" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				<td align="right">Номенклатура</td>
				<td>
					<select name="nIDNomenclature" id="nIDNomenclature" class="select200" />
				</td>
				
				<td>&nbsp;&nbsp;</td>
				

				<td align="right">
					<div id="type_caption">Тип Склад</div>
					<div id="type_caption_obj">Собственост</div>
				</td>
				<td>
					<div id="type_select">
						<select name="sStoragehouseType" id="sStoragehouseType" class="select150" onchange="nullStorage();">
							<option value="">-- Всички --</option>
							<option value="new">Нова Техника</option>
							<option value="recik">Рециклирана Техника</option>
							<option value="removed">Свалена Техника</option>
						</select>
					</div>
					<div id="type_select_obj">
						<select name="sStoragehouseTypeObj" id="sStoragehouseTypeObj" class="select150" onchange="nullStorage();">
							<option value="">-- Всички --</option>
							<option value="clientown">на клиента</option>
							<option value="firmown">на фирмата</option>
						</select>
					</div>
				</td>
			
				<td colspan="3">&nbsp;</td>
			</tr>
			
			<tr>
				<td colspan="10">&nbsp;</td>
				<td align="right"><button type="button" name="Button" class="search" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
		</table>
	</center>
	
	<hr>
	
	<div id="result" class="ui-monitor-result"></div>

</form>

<script>
	onInit();
</script>
