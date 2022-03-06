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
				//document.getElementById( 'type_caption_obj' ).style.display = "block";
				document.getElementById( 'type_select_obj' ).style.display = "block";
			}
			else
			{
				//document.getElementById( 'type_caption_obj' ).style.display = "none";
				document.getElementById( 'type_select_obj' ).style.display = "none";
			}
			
			
			if( $('sStorageType').value == 'storagehouse' )
			{
				//document.getElementById( 'type_caption' ).style.display = "block";
				document.getElementById( 'type_select' ).style.display = "block";
			}
			else
			{
				//document.getElementById( 'type_caption' ).style.display = "none";
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
<div class="w-100 px-0 mx-0 mb-2 bg-light">
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nIDStoragehouse" id="nIDStoragehouse" value="0">

	{include file="tabs_setup_ppp.tpl"}

	<div id="filter" class="container pt-2 pb-2">
		<div class="row">
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-briefcase fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select name="nIDFirm" id="nIDFirm" class="form-control" onchange="loadXMLDoc2('loadOffices')" ></select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-briefcase fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select name="nIDOffice" id="nIDOffice" class="form-control" ></select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></i>
					</div>
					<select name="sStorageType" id="sStorageType" class="form-control" onchange="nullStorage();">
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="object">Обект</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-database fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></i>
					</div>
					<input name="sStoragehouse" id="sStoragehouse" placeholder="Склад" class="form-control" suggest="suggest" queryType="statesStorage" queryParams="sStorageType;nIDOffice;nIDFirm" onchange="changesHandler();" />
				</div>
			</div>
		</div>

		<div class="row py-2">
			<div class="col" id="type_select">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-tags fa-fw" data-fa-transform="right-22 down-10" title="Тип склад..."></i>
					</div>
					<select name="sStoragehouseType" id="sStoragehouseType" class="form-control" onchange="nullStorage();"></select>
				</div>
			</div>
			<div class="col" id="type_select_obj" >
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-tags fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></i>
					</div>
					<select name="sStoragehouseTypeObj" id="sStoragehouseTypeObj" class="form-control" onchange="nullStorage();">
						<option value="">-- Собственост --</option>
						<option value="clientown">на клиента</option>
						<option value="firmown">на фирмата</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-th-list fa-fw" data-fa-transform="right-22 down-10" title="Тип артикул..."></span>
					</div>
					<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="form-control" onchange="loadXMLDoc2('loadNomenclatures')" ></select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="far fa-th-listfa-fw" data-fa-transform="right-22 down-10" title="Артикул..."></i>
					</div>
					<select name="nIDNomenclature" id="nIDNomenclature" class="form-control" ></select>
				</div>
			</div>
			<div class="col">

			</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="btn-group">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="far fa-filter fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></i>
						</div>
						<select class="form-control" name="schemes" id="schemes"><option id="0" value="0">-- Изберете --</option></select>
					</div>
					<button type="button" class="btn btn-sm btn-success" name="Button5" id="b25" title="Нов филтър" onclick="openFilter( 1 );">
						<i class="far fa-plus"></i>	</button>
					<button type="button" class="btn btn-sm btn-secondary" name="Button4" id="b25" title="Редактиране на филтър" onclick="openFilter( 2 );">
						<i class="far fa-pencil-alt"></i></button>
					<button type="button" class="btn btn-sm btn-danger" name="Button3" id="b25" title="Премахване на филтър" onclick="deleteFilter( schemes );">
						<i class="far fa-minus"></i></button>
				</div>
			</div>
			<div class="col">
{*				<div id="type_caption" >Тип Склад:</div>*}
{*				<div id="type_caption_obj">Собственост:</div>*}
			</div>
			<div class="col">

			</div>
			<div class="col">
				<button name="Button" class="btn btn-sm btn-info float-right" onclick="loadXMLDoc2('result');">	<i class="far fa-search"></i> Търси	</button>
			</div>
		</div>

	</div>

	<div id="result"></div>

</form>
</div>

<script>
	onInit();
</script>