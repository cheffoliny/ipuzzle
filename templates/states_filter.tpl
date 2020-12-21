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
			loadXMLDoc2( 'load');
		}
		
		function nullStorage()
		{
			document.getElementById( 'sStoragehouse' ).value = '';
			document.getElementById( 'nIDStoragehouse' ).value = 0;
			
			var type = $('sStorageType').value;
			
			if( type == 'storagehouse' )
			{
				$('sStoragehouseType').disabled = false;
			}
			else
			{
				$('sStoragehouseType').disabled = 'disabled';
			}
		}
		
		function getResult()
		{
			loadXMLDoc2( 'save' );
			rpc_on_exit = function()
			{
				var nID = $('nID').value;
				window.location = 'page.php?page=states_filter_fields&id=' + nID;
				rpc_on_exit = function() {}
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" name="nIDStoragehouse" id="nIDStoragehouse" value="0">
	
	<div class="page_caption">
		Редактиране на филтър
	</div>
	
	<table cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter">
  		<tr>
  			<td>
  				{include file=states_filter_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
	
	<table border="0" class="input">
		<tr class="even">
			<td align="right">
				Име
			</td>
			<td>
				<input type="text" name="sName" id="sName" style="width:300px;" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset>
				<legend>Критерии за търсене</legend>
				<table class="input">
					<tr class="even">
						<td align="right">
							Фирма
						</td>
						<td>
							<select name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Регион
						</td>
						<td>
							<select name="nIDOffice" id="nIDOffice" />
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Тип
						</td>
						<td>
							<select name="sStorageType" id="sStorageType" class="select150" onchange="nullStorage();"/>
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Склад
						</td>
						<td>
							<input name="sStoragehouse" id="sStoragehouse" class="inp200" suggest="suggest" queryType="statesStorage" queryParams="sStorageType;nIDOffice;nIDFirm"  />
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Тип Номенклатура
						</td>
						<td>
							<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="select200" onchange="loadXMLDoc2('loadNomenclatures');" />
						</td>
					</tr>
					<tr class="odd">
						<td align="right">
							Номенклатура
						</td>
						<td>
							<select name="nIDNomenclature" id="nIDNomenclature"  />
						</td>
					</tr>
					<tr class="even">
						<td align="right">
							Тип Склад
						</td>
						<td>
							<select name="sStoragehouseType" id="sStoragehouseType" />
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td align="right">
				<input type="checkbox" class="clear" name="nDefault" id="nDefault" />
			</td>
			<td>
				филтър по подразбиране
			</td>
		</tr>
		<tr class="odd" >
			<td width="250">&nbsp;</td>
			<td style="text-align:right;">
				<br><br>
				<button type="button" class="search" onClick="getResult();"> Запиши </button>
				<button type="button" onClick="window.opener.loadXMLDoc2( 'load' ); parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>


</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}