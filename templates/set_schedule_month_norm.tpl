{literal}
	<script>
		rpc_debug = true;
		
		function setFields()
		{
			var aYears = window.opener.$("sYearsFound").value.split( "|" );
			
			var nIndex = 0;
			while( nIndex < aYears.length )
			{
				var nYear = aYears[nIndex];
				
				for( var i = 1; i <= 12; i++ )
				{
					var sName = "shifts" + nYear + "[" + i + "]";
					var sNameH = "hours" + nYear + "[" + i + "]";
					var sNamePrefix = "my_rpc_prefixshifts" + nYear + "[" + i + "]";
					var sNamePrefixH = "my_rpc_prefixhours" + nYear + "[" + i + "]";
					
					if( window.opener.document.getElementById( sName ) )
					{
						var myObject = window.opener.document.getElementById( sName );
						var myObjectH = window.opener.document.getElementById( sNameH );
					}
					else
					{
						var myObject = window.opener.document.getElementById( sNamePrefix );
						var myObjectH = window.opener.document.getElementById( sNamePrefixH );
					}
					
					myObject.style.textAlign = "right";
					myObject.style.border = "0px";
					myObject.style.background = "transparent";
					
					myObject.blur();
					myObject.disabled = "disabled";
					
					myObject.onkeydown = function() { window.opener.onKeyPressOnItem( this.id, "S" ); }
					myObject.onblur = function() { window.opener.onBlurItem( this.id, "S" ); }
					
					myObjectH.style.textAlign = "right";
					myObjectH.style.border = "0px";
					myObjectH.style.background = "transparent";
					
					myObjectH.blur();
					myObjectH.disabled = "disabled";
					
					myObjectH.onkeydown = function() { window.opener.onKeyPressOnItem( this.id, "H" ); }
					myObjectH.onblur = function() { window.opener.onBlurItem( this.id, "H" ); }
				}
				
				nIndex++;
			}
		}
		
		function Save()
		{
			rpc_on_exit = function()
			{
				window.opener.rpc_on_exit = function()
				{
					setFields();
					
					rpc_on_exit = function() {}
					window.close();
				}
				
				window.opener.loadXMLDoc2( "result" );
			}
			
			loadXMLDoc2( 'save' );
		}
	</script>
{/literal}

<div class="content">
	<form method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}" />
		<input type="hidden" id="nIDToUpdate" name="nIDToUpdate" value="0" />
		
		<div class="page_caption">{if $nID}Редактиране на Норми{else}Нови Норми{/if}</div>
		<fieldset>
			<legend>Месечни Норми:</legend>
			<table class="input">
			
				<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
				
				<tr class="odd">
					<td align="left"><b>Година:</b>&nbsp;</td>
					<td align="left">
						<select name="nYear" id="nYear" class="select100" onchange="loadXMLDoc2( 'changeYear' );" />
					</td>
				</tr>
				
				<tr class="odd">
					<td align="left"><b>Месец:</b>&nbsp;</td>
					<td align="left">
						<input type="text" name="sMonth" id="sMonth" class="clear" size="10" disabled>
					</td>
				</tr>
				
				<tr class="odd">
					<td colspan="2">&nbsp;</td>
				</tr>
				
				<tr class="odd">
					<td align="left">Макс. брой смени:&nbsp;</td>
					<td align="left">
						<input type="text" name="nNormShifts" id="nNormShifts" style="width: 60px; text-align: right;" onkeypress="return formatDigits( event );" maxlength="6" />
					</td>
				</tr>
				
				<tr class="odd">
					<td align="left">Макс. брой часове:&nbsp;</td>
					<td align="left">
						<input type="text" name="nNormHours" id="nNormHours" style="width: 60px; text-align: right;" onkeypress="return formatDigits( event );" maxlength="6" />
					</td>
				</tr>
				
				<tr class="odd"><td colspan="2" style="height: 8px;"></td></tr>
			
			</table>
		</fieldset>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="button" onClick="Save();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	
	</form>
</div>

<script>
	loadXMLDoc2( 'load' );
</script>