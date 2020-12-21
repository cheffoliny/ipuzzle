{literal}

	<script>
		rpc_debug=true;
		
		function submit_form()
		{
			loadXMLDoc2( 'save', 3 );
			return false;
		}
		
		function setRequestElement( id )
		{
			
			if( $('nForRead').value == 0 )
			{
				if( $('nID').value != 0 )
				{
					var params = 'id=' + id;
					
					params += '&id_request=' + $('nID').value;
					
					dialogRequestNomenclature( params );
				}
				else
				{
					rpc_on_exit = function( nCode )
					{
						if( !parseInt( nCode ) )
						{
							var params = 'id=' + id;
							
							params += '&id_request=' + $('nID').value;
							
							dialogRequestNomenclature( params );
						}
						
						rpc_on_exit = function( nCode ) {}
					}
					
					loadXMLDoc2( 'save' );
				}
			}
		}

		function deleteRequestElement( id )
		{
			if( $('nForRead').value == 0 )
			{
				if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
				{
					$('nIDElement').value = id;
					loadXMLDoc2( 'delete', 1 );
				}
			}
		}

	</script>

{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return submit_form();">
		<input type="hidden" name="nID" id="nID" value="{$nID}">
		<input type="hidden" name="nForRead" id="nForRead" value="0">
		<input type="hidden" name="nIDElement" id="nIDElement" value="0">
		
		<div class="page_caption">Попълване на Задача</div>
		
		<br />

		
		<fieldset>
			<legend>От Склад:</legend>
			<table class="input">
				<tr class="odd">
					<td width="80px">Регион:</td>
					<td>
						<select name="nIDOffice" id="nIDOffice" class="select300" onchange="$('nIDStoragehouse').value=0; loadXMLDoc2( 'refreshStoragehouses' );" />
					</td>
				</tr>
				<tr class="even">
					<td>Склад:</td>
					<td>
						<select name="nIDStoragehouse" id="nIDStoragehouse" class="select200" onchange="loadXMLDoc2( 'refreshMOL' );" />
					</td>
				</tr>
				<tr class="odd">
					<td>МОЛ:</td>
					<td>
						<input type="text" name="sMOL" id="sMOL" class="inp200" readonly="readonly" />
					</td>
				</tr>
			</table>
		</fieldset>
		
		<table class="input">
			<tr class="even">
				<td width="80px">Към Склад:</td>
				<td>
					<select name="nIDToStoragehouse" id="nIDToStoragehouse" class="select200"/>
				</td>
			</tr>
		</table>
		
		<table class="input">
			<tr class="even">
				<td>
					<fieldset>
						<legend>Номенклатури:</legend>
						<table class="page_data">
							<tr>
								<td class="buttons">
									<button id="add" onclick="setRequestElement( 0 );" style="width: 20px;"><img src="images/plus.gif"></button>
								</td>
							</tr>
						</table>
						
						<div style="height: 180px; overflow: auto;">
							<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<fieldset>
						<legend>Коментар:</legend>
						<textarea cols="80" rows="3" id="sComment" name="sComment"></textarea>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td style="text-align:right;">
					<button id="send" type="submit" class="search">{if $nID} Потвърди {else} Запиши {/if}</button>
					<button onClick="opener.loadXMLDoc2( 'result' ); parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

{literal}
	<script>
		
		rpc_on_exit = function( nCode )
		{
			if( !parseInt( nCode ) )
			{
				if( $('nForRead').value == 1 )
				{
					document.getElementById( 'nIDOffice' ).disabled = "disabled";
					document.getElementById( 'nIDStoragehouse' ).disabled = "disabled";
					document.getElementById( 'nIDToStoragehouse' ).disabled = "disabled";
					document.getElementById( 'add' ).disabled = "disabled";
					document.getElementById( 'sComment' ).disabled = "disabled";
					document.getElementById( 'send' ).disabled = "disabled";
				}
			}
			
			rpc_on_exit = function( nCode ) {}
		}
		
		loadXMLDoc2( 'result' );
		
	</script>
{/literal}