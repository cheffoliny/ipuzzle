{literal}
	<script>
		rpc_debug = true;
		
		function fixFields()
		{
			var aYears = $("sYearsFound").value.split( "|" );
			
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
					
					if( document.getElementById( sName ) )
					{
						var myObject = document.getElementById( sName );
						var myObjectH = document.getElementById( sNameH );
					}
					else
					{
						var myObject = document.getElementById( sNamePrefix );
						var myObjectH = document.getElementById( sNamePrefixH );
					}
					
					myObject.style.textAlign = "right";
					myObject.style.border = "0px";
					myObject.style.background = "transparent";
					
					myObject.blur();
					myObject.disabled = "disabled";
					
					myObject.onkeydown = function() { onKeyPressOnItem( this.id, "S" ); }
					myObject.onblur = function() { onBlurItem( this.id, "S" ); }
					
					myObjectH.style.textAlign = "right";
					myObjectH.style.border = "0px";
					myObjectH.style.background = "transparent";
					
					myObjectH.blur();
					myObjectH.disabled = "disabled";
					
					myObjectH.onkeydown = function() { onKeyPressOnItem( this.id, "H" ); }
					myObjectH.onblur = function() { onBlurItem( this.id, "H" ); }
				}
				
				nIndex++;
			}
		}
		
		function onBlurItem( sField, sMode )
		{
			rpc_on_exit = function()
			{
				fixFields();
				
				rpc_on_exit = function()
				{
					fixFields();
					
					rpc_on_exit = function() {}
				}
				
				loadXMLDoc2( 'result' );
			}
			
			var nValue = document.getElementById( sField ).value;
			loadXMLDoc2( "saveShiftData&sField=" + sField + '&nValue=' + nValue + '&sMode=' + sMode );
		}
		
		function onKeyPressOnItem( sField, sMode )
		{
			var intKey = 0;
			if( window.event )intKey = event.keyCode;
			
			if( intKey == 13 )
			{
				onBlurItem( sField, sMode );
			}
		}
		
		function onClickItemShifts( nMonth, nYear )
		{
			//alert( nMonth + " " + nYear );
			var sName = "shifts" + nYear + "[" + nMonth + "]";
			var sNamePrefix = "my_rpc_prefixshifts" + nYear + "[" + nMonth + "]";
			
			if( document.getElementById( sName ) )
				var myObject = document.getElementById( sName );
			else
				var myObject = document.getElementById( sNamePrefix );
			
			myObject.disabled = "";
			myObject.focus();
			myObject.select();
		}
		
		function onClickItemHours( nMonth, nYear )
		{
			//alert( nMonth + " " + nYear );
			var sName = "hours" + nYear + "[" + nMonth + "]";
			var sNamePrefix = "my_rpc_prefixhours" + nYear + "[" + nMonth + "]";
			
			if( document.getElementById( sName ) )
				var myObject = document.getElementById( sName );
			else
				var myObject = document.getElementById( sNamePrefix );
			
			myObject.disabled = "";
			myObject.focus();
			myObject.select();
		}
		
		function editNorms( id )
		{
			dialogScheduleMonthNorm( id );
		}
	</script>
{/literal}

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0" />
	<input type="hidden" name="sYearsFound" id="sYearsFound" value="af" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Месечни Норми</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
</form>

{literal}
	<script>
		rpc_on_exit = function()
		{
			fixFields();
			
			rpc_on_exit = function() {}
		}
		
		loadXMLDoc2( 'result' );
	</script>
{/literal}