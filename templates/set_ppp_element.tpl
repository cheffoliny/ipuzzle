{literal}
	<script>
		rpc_debug = true;
		
		function selectNomenclature()
		{
			document.getElementById( 'nIDScheme' ).value = 0;
			$('nMode').value = 0;
			
			var oDivNomenclatures = document.getElementById( "div_nomenclatures" );
			var oAddNomenclatures = document.getElementById( "add_nom1" );
			oDivNomenclatures.style.display = "block";
			oAddNomenclatures.disabled 		= "";
			
			if( document.getElementById( 'nIDNomenclature' ).value != 0 )
			{
				rpc_on_exit = function( nCode )
				{
					if( !parseInt( nCode ) )
					{
						document.getElementById( 'sMeasure' ).innerHTML = $('sHMeasure').value;
						
						if( $('nLCCreateObject').value == "1" && $('nCallerID').value == "1" )
						{
							document.getElementById( "nIDNomenclature" ).disabled 		= "disabled";
							document.getElementById( "nIDNomenclatureType" ).disabled 	= "disabled";
							document.getElementById( "nIDScheme" ).disabled 			= "disabled";
						}
						
						if( $("nID").value != 0 && $("nAddFirst").value == 0 )
						{
							addNomenclature();
							$("nAddFirst").value = 1;
						}
					}
					
					rpc_on_exit = function( nCode ) {}
				}
				
				loadXMLDoc2( 'getMeasure' );
			}
			else
			{
				document.getElementById( 'sMeasure' ).innerHTML = "";
			}
		}
		
		function selectScheme()
		{
			document.getElementById( 'nIDNomenclatureType' ).value = 0;
			document.getElementById( 'nIDNomenclature' ).value = 0;
			document.getElementById( 'sMeasure' ).innerHTML = "";
			loadXMLDoc2( 'refresh' );
			$('nMode').value = 1;
			
			var oDivNomenclatures = document.getElementById( "div_nomenclatures" );
			var oAddNomenclatures = document.getElementById( "add_nom1" );
			oDivNomenclatures.style.display = "none";
			oAddNomenclatures.disabled = "disabled";
		}

		function is_numeric( mixed_var )
		{
			return ( typeof( mixed_var ) === 'number' || typeof( mixed_var ) === 'string' ) && mixed_var !== '' && !isNaN( mixed_var );
		}
		
		function getAvailableCount( sNomenclatureName )
		{
			try
			{
				var bSeekingMode = false;
				var sNumber = "";
				
				for( var i = 0; i <= sNomenclatureName.length; i++ )
				{
					var sCC = sNomenclatureName.charAt( i );
					
					if( sCC == "(" ) { bSeekingMode = true; continue; }
					if( sCC == ")" || ( !is_numeric( sCC ) && sCC != "." ) ) { bSeekingMode = false; continue; }
					
					if( ( bSeekingMode == true && sCC != " " ) && ( is_numeric( sCC ) || sCC == "." ) ) sNumber += "" + sCC;
				}
			}
			catch( e ) {}
			
			return parseFloat( sNumber );
		}
		
		function addNomenclature()
		{
			
			if( $("nMode").value == 0 )
			{
				var nID = document.getElementById('nIDNomenclature').value;
				
				if( nID == 0 ) return false;
				
				var aOpt = document.getElementById('nIDNomenclature').options;
				
				var sName = "";
				
				for( i = 0; i < aOpt.length; i++ )
				{
					if( aOpt[i].value == nID ) sName = aOpt[i].innerHTML;
				}

				var nClientOwn = document.getElementById( "nClientOwn" ).checked == true ? 1 : 0;
				var nCount = document.getElementById( "nCount" ).value;

				// Check Nomenclature Count
				// -- Get Same Nomenclatures Counts
				aElements = document.getElementsByTagName( "input" );
				
				var aInfo = new Array();
				var nCountSoFar = 0;
				
				for( var i = 0; i < aElements.length; i++ )
				{
					if( aElements[i].type == "hidden" && aElements[i].id.substr( 0, 5 ) == "sInfo" )
					{
						aInfo = aElements[i].value.split( "," );
						
						if( aInfo[0] != "undefined" && aInfo[2] != "undefined" && aInfo[0] == nID ) nCountSoFar += parseFloat( aInfo[2] );
					}
				}
				// -- End Get Same Nomenclatures Counts
				
				if( ( parseFloat( nCount ) + nCountSoFar ) > getAvailableCount( sName ) ) { alert( "Избраното количество надвишава наличното!" ); return false; }
				// End Check Nomenclature Count
				
				// Добавяне на елемент в таблицата:
				var oNomenclatures = document.getElementById( "nomenclatures" ).getElementsByTagName( 'tbody' )[0];
				
				// Нов Ред
				var nUniqueKey = $("nUniqueKey").value;
				nUniqueKey++;
				$("nUniqueKey").value = nUniqueKey;
				var oNewRow = document.createElement( 'tr' );
				oNewRow.setAttribute( "id", "Row" + nID );
				
				var oDataIDName = document.createElement( 'td' );
				oDataIDName.innerHTML = "<input type=\"hidden\" id=\"sInfo" + nUniqueKey + "\" name=\"sInfo" + nUniqueKey + "\" value=\"" + nID + "," + nClientOwn + "," + nCount + "\">" + sName;

				var oDataClientOwn = document.createElement( 'td' );
				oDataClientOwn.innerHTML = ( nClientOwn == 1 ) ? "Да" : "Не";
				var oDataCount = document.createElement( 'td' );
				oDataCount.innerHTML = nCount;
				var oDataDelButton = document.createElement( 'td' );
				oDataDelButton.innerHTML = "<button style=\"width: 20px; height: 20px;\" id=\"Delete" + nID + "\" name=\"" +nID+ "\" onclick=\"deleteNomenclature( this.name );return false;\"><img src=\"images/cancel.gif\" /></button>";
				
				oNomenclatures.appendChild( oNewRow );
				oNewRow.appendChild( oDataIDName );
				oNewRow.appendChild( oDataClientOwn );
				oNewRow.appendChild( oDataCount );
				oNewRow.appendChild( oDataDelButton );
				
				return true;
			}
		}

		
		function deleteNomenclature( nID )
		{
			if( nID == 0 )
			{
				alert( "Невалидна номенклатура!" );
				return false;
			}
			
			var oNomenclatures = document.getElementById( "nomenclatures" ).getElementsByTagName( 'tbody' )[0];
			
			if( oNomenclatures && document.getElementById( "Row" + nID ) )
			{
				var oRowToDelete = document.getElementById( "Row" + nID );
				oNomenclatures.removeChild( oRowToDelete );
			}
			
			return true;
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return loadXMLDoc2( 'save', 3 );">
		<!-- Hidden Info -->
		<input type="hidden" id="nID" name="nID" value="{$nID}"/>
		<input type="hidden" id="nIDPPP" name="nIDPPP" value="{$nIDPPP}"/>
		<input type="hidden" id="nIDStorage" name="nIDStorage" value="{$nIDStorage}"/>
		<input type="hidden" id="sStorageType" name="sStorageType" value="{$sStorageType}"/>
		<input type="hidden" id="nCallerID" name="nCallerID" value="{$nCallerID}"/>
		
		<input type="hidden" id="nAddFirst" name="nAddFirst" value="0"/>
		<input type="hidden" id="nUniqueKey" name="nUniqueKey" value="0"/>
		<input type="hidden" id="nMode" name="nMode" value="0"/>
		<input type="hidden" id="nInitCount" name="nInitCount" value="0"/>
		<input type="hidden" id="nLCCreateObject" name="nLCCreateObject" value="0"/>
		
		<input type="hidden" id="sHMeasure" name="sHMeasure" value=""/>
		<!-- End Hidden Info -->
		
		<div class="page_caption">{if $nID}Редактиране на Номенклатура{else}Нова Номенклатура{/if}</div>
		<table class="input">
			<tr>
				<td width="480px">
					<table class="input">
						<tr class="odd"><td colspan="2" style="height: 8px;">&nbsp;</td></tr>
						
						<tr class="even">
							<td width="150">Тип Номенклатура:</td>
							<td>
								<select name="nIDNomenclatureType" id="nIDNomenclatureType" class="select200" onchange="loadXMLDoc2('refresh'); selectNomenclature();" />
							</td>
						</tr>
						<tr class="odd">
							<td width="150">Наименование:</td>
							<td>
								<select name="nIDNomenclature" id="nIDNomenclature" class="select300" onchange="selectNomenclature();" />
							</td>
						</tr>
						
						<tr class="even">
							<td width="150">Шаблон:</td>
							<td>
								<select name="nIDScheme" id="nIDScheme" class="select300" onchange="selectScheme();" />
							</td>
						</tr>
						
						<tr class="odd">
							<td width="150">Собственост на Клиента:</td>
							<td><input type="checkbox" name="nClientOwn" id="nClientOwn" class="clear"></td>
						</tr>
					</table>
					<table class="input">
						<tr class="even">
							<td width="150">Количество:</td>
							<td width="50">
								<input type="text" name="nCount" id="nCount" class="inp50" maxlength="6" onkeypress="return formatMoney(event);" />
							</td>
							<td>
								<div id="sMeasure"></div>
							</td>
							<td>
								<button id="add_nom1" name="add_nom1" onclick="addNomenclature(); return false;" class="search"> Добави </button>
							</td>
						</tr>
						
						<tr class="odd"><td colspan="4" style="height: 5px;">&nbsp;</td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="480px" height="160px" valign="top" align="left">
					<div style="height: 160px; overflow: auto;" id="div_nomenclatures">
						<table class="input" border="1" id="nomenclatures" width="100%">
							<tr>
								<th style="background-color: #FFFFC8;" width="70%">Номенклатура</th>
								<th style="background-color: #FFFFC8;">На кл.</th>
								<th style="background-color: #FFFFC8;">Кол.</th>
								<th align="center" style="background-color: #FFFFC8;"><img src="images/minus_blue.gif" /></th>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
		
		<table class="input">
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<!-- <button onClick="parent.window.close();"> Затвори </button> -->
				</td>
			</tr>
		</table>
	</form>
</div>

{literal}
	<script>
		loadXMLDoc2( 'load' );
		
		rpc_on_exit = function( nCode )
		{
			if( !parseInt( nCode ) )
			{
				if( $('nLCCreateObject').value == "1" && $('nCallerID').value == "1" )
				{
					document.getElementById( "nIDNomenclature" ).disabled = "disabled";
					document.getElementById( "nIDNomenclatureType" ).disabled = "disabled";
					document.getElementById( "nIDScheme" ).disabled = "disabled";
				}
			}
			
			rpc_on_exit = function( nCode ) {}
		}
	</script>
{/literal}