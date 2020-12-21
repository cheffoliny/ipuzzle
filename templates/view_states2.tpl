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
			loadXMLDoc2('load');
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
			
//			document.getElementById( 'storagetype' ).innerHTML = sStorage;
			jQuery( '#sStoragehouse' ).attr('placeholder',sStorage);
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
					dialogIDOldObjectArchiv(aBothIDs[1]);
					//dialogObjectInfo( sParams );
				}
			}
		}
		
		function openObjectMessage(id) {
			if ( id ) {
				var aBothIDs = id.split( "," );
				
				if ( aBothIDs[1] ) {
					dialogObjectMessages(aBothIDs[1]);
				}
			}			
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nIDStoragehouse" id="nIDStoragehouse" value="0">

	<div class="page_caption row" border="0">
		<div class="col-lg-3 col-sm-0">Наличности</div>
		<div class="col-lg-9 col-sm-12" align="right">
			<div class="btn-group">
				<div class="input-group input-group-sm">
					<span class="input-group-addon"><i class="far fa-filter"></i></span>
					<select class="form-control form-control-select100" name="schemes" id="schemes"><option id="0" value="0">---Изберете---</option></select>
				</div>
				<button type="button" class="btn btn-sm btn-success" name="Button5" id="b25" title="Нов филтър" onclick="openFilter( 1 );">
					<i class="far fa-plus"></i>
				</button>
				<button type="button" class="btn btn-sm btn-secondary" name="Button4" id="b25" title="Редактиране на филтър" onclick="openFilter( 2 );">
					<i class="far fa-pencil-alt"></i>
				</button>
				<button type="button" class="btn btn-sm btn-danger" name="Button3" id="b25" title="Премахване на филтър" onclick="deleteFilter( schemes );">
					<i class="far fa-minus"></i>
				</button>
			</div>
		</div>
	</div>

	<div class="container-fluid pt-2 pb-2 mb-2 bg-light" border="0" id="filter">
		<table class="table-sm table-borderless" align="center">
			<tr>
				{*<td align="right">Фирма:</td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-briefcase"></i></span>
						<select name="nIDFirm" id="nIDFirm" class="form-control form-control-select200" onchange="loadXMLDoc2('loadOffices')" />
					</div>

				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				{*<td align="right">Регион:</td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-briefcase"></i></span>
						<select name="nIDOffice" id="nIDOffice" class="form-control form-control-select200" />
					</div>
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				{*<td align="right">Тип:</td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-tag"></i></span>

						<select name="sStorageType" id="sStorageType" class="form-control form-control-select150" onchange="nullStorage();">
							<option value="storagehouse">Склад</option>
							<option value="person">Служител</option>
							<option value="object">Обект</option>
						</select>
					</div>
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				{*<td align="right"><div id="storagetype">Склад:</div></td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-home"></i></span>
						<input name="sStoragehouse" id="sStoragehouse" placeholder="Склад" class="form-control form-control-inp200" suggest="suggest" queryType="statesStorage" queryParams="sStorageType;nIDOffice;nIDFirm" onchange="changesHandler();" />
					</div>
				</td>
			</tr>
			
			<tr>
				{*<td align="right">Тип Номенклатура:</td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-th-list"></i></span>
						<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="form-control form-control-select200" onchange="loadXMLDoc2('loadNomenclatures')" />
					</div>
				</td>
				
				<td>&nbsp;&nbsp;</td>
				
				{*<td align="right">Номенклатура:</td>*}
				<td>
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><i class="far fa-th-list"></i></span>
						<select name="nIDNomenclature" id="nIDNomenclature" class="form-control form-control-select200" />
					</div>
				</td>
				
				<td>&nbsp;&nbsp;</td>
				

				<td align="right" style="display: none;">
					<div id="type_caption" >Тип Склад:</div>
					<div id="type_caption_obj">Собственост:</div>
				</td>
				<td>

						{*<span class="input-group-addon"><i class="far fa-tags"></i></span>*}
						<div id="type_select">
							<div class="input-group input-group-sm">
								<span class="input-group-addon"><i class="far fa-tags"></i></span>
								<select name="sStoragehouseType" id="sStoragehouseType" class="form-control form-control-select150" onchange="nullStorage();">
									{*<option value="">-- Всички --</option>*}
									{*<option value="new">Нова Техника</option>*}
									{*<option value="recik">Рециклирана Техника</option>*}
									{*<option value="removed">СВАЛЕНА ТЕХНИКА</option>*}
									{*<option value="ready">ГОДНА ТЕХНИКА</option>*}
								</select>
							</div>
						</div>
						<div id="type_select_obj">
							<div class="input-group input-group-sm">
								<span class="input-group-addon"><i class="far fa-tags"></i></span>
								<select name="sStoragehouseTypeObj" id="sStoragehouseTypeObj" class="form-control form-control-select150" onchange="nullStorage();">
									<option value="">-- Собственост --</option>
									<option value="clientown">на клиента</option>
									<option value="firmown">на фирмата</option>
								</select>
							</div>
						</div>
					</div>
				</td>

				{*<td colspan="3">&nbsp;</td>*}
				<td colspan="3" >
					<button name="Button" class="btn btn-sm btn-info float-right" onclick="loadXMLDoc2('result');">
						<i class="far fa-search"></i>&nbsp;
						Търси
					</button>
				</td>
				</tr>

				{*<tr>*}
				{*<td colspan="10">&nbsp;</td>*}
				{*<td align="right">*}
				{*<button name="Button" class="btn btn-sm btn-info" onclick="loadXMLDoc2('result');">*}
				{*<i class="far fa-search"></i>&nbsp;*}
				{*Търси*}
				{*</button>*}
				{*</td>*}
				{*</tr>*}
		</table>

	
	<div id="result"></div>

</form>

<script>
	onInit();
</script>