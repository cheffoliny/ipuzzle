{literal}
	<script>
		rpc_debug = true;
		
		function setPPPElement( id )
		{
			//if( $('id_person').value == $('id_log_person').value ) {
			
				if( $('nIDCurrentPPP').value != 0 && $('nPPPClosed').value == '0' )
				{
					if( id != 0 || $('nLCCreateObject').value == "0" )
					{
						var params = 'id=' + id;
						
						params += '&id_ppp=' + $('nIDCurrentPPP').value;
						params += '&id_caller=1';
						
						dialogPPPElement( params );
					}
				}
			//}
		}
		
		function deletePPPElement( id )
		{
			//if( $('id_person').value == $('id_log_person').value ) {
			
				if( $('nPPPClosed').value == '0' )
				{
					if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
					{
						$('nIDElement').value = id;
						loadXMLDoc2( 'delete', 1 );
					}
				}
			//}
		}
		
		function openPPP( id )
		{
		//	if( $('id_person').value == $('id_log_person').value ) {
			
				var params = '';
				
				if( id != 0 )
				{
					params = 'id=' + id;
				}
				else
				{
					params = 'id=' + id + '&';
					if( $('nIDObject').value != '0' )
					{
						params += 'id_object=' + $('nIDObject').value + '&';
					}
					params += 'id_limit_card=' + $('nIDLimitCard').value;
				}
				
				dialogPPP2( params );
			//}
		}
		
		function processPPP()
		{
			
			//if( $('id_person').value == $('id_log_person').value ) {
				
				if( $('nLCClosed').value == '0' )
				{
					document.getElementById( 'sProblem' ).value = "";
					
					rpc_on_exit = function( nCode )
					{
						if( !parseInt( nCode ) )
						{
							switch( $('sProblem').value )
							{
								case "Empty Field":
									openPPP( 0 );
								break;
								
								case "No PPP":
									if( confirm( 'Не съществува ППП с този номер. Създаване на нов?' ) )
									{
										openPPP( 0 );
									}
								break;
								
								case "No Object":
									alert( "ППП няма зададен обект!" );
								break;
								
								case "Canceled":
									alert( "Избрания ППП съществува, но е анулиран!" );
								break;
								
								case "Closed LC":
									loadXMLDoc2( 'result' );
								break;
								
								default:
									loadXMLDoc2( 'result' );
								break;
							}
						}
						
						rpc_on_exit = function( nCode ) {}
					}
					
					loadXMLDoc2( 'processPPP' );
				}
			
			//}
		}
		
		function switch_ppp( id_ppp )
		{
			$('nIDCurrentPPP').value = id_ppp
			loadXMLDoc2( 'result' );
		}
		
		function getWindowWidth()
		{
			var ww = 0;
			if( self.innerWidth )
				ww = self.innerWidth;
			else if( document.documentElement && document.documentElement.clientWidth )
				ww = document.documentElement.clientWidth;
			else if( document.body )
				ww = document.body.clientWidth;
			
			return ww;
		}
		
		function getWindowHeight()
		{
			var wh = 0;
			if( self.innerHeight )
				wh = self.innerHeight;
			else if( document.documentElement && document.documentElement.clientHeight )
				wh = document.documentElement.clientHeight;
			else if( document.body )
				wh = document.body.clientHeight;
			
			return wh;
		}
		
		function resizeHandler()
		{
			var winHeight = getWindowHeight();
			var winWidth = getWindowWidth();
			
			if( document.getElementById( 'tabcontainer' ) && document.getElementById( 'ppplist' ) && document.getElementById( 'result' ) )
			{
				if( winHeight != 0 && winWidth != 0 )
				{
					if( winHeight >= 150 )
					{
						document.getElementById( 'tabcontainer' ).style.height = ( winHeight - 115 ) + 'px';
						document.getElementById( 'ppplist' ).style.height = document.getElementById( 'tabcontainer' ).style.height;
						document.getElementById( 'result' ).style.height = document.getElementById( 'tabcontainer' ).style.height;
					}
				}
			}
		}
	</script>
	
	<style>
		#active,
		#inactive
		{
			text-align: center !important;
	 		padding: 3px 15px 3px 15px;
		}
		
		#tabs td
		{
			white-space: nowrap !important;
		}
		
		#ppplist,
		#result
		{
			overflow-x: hidden;
			overflow-y: auto;
		}
		
		input.caption
		{
			color: #fefefe;
			height: 26px;
			padding-left: 6px;
			padding-top: 3px;
			font-size: 16px;
			font-weight: bold;
			border: 0px;
			width: 100%;
			background-color: #617cb3;
		}
	</style>
{/literal}

<body onresize="resizeHandler();" onload="resizeHandler();">
	<form action="" name="form1" id="form1" onSubmit="return false;">
		<input type="hidden" name="id_person" id="id_person" value="0">
		<input type="hidden" name="id_log_person" id="id_log_person" value="{$nIDLogPerson|default:0}">
	
		<input type="hidden" name="sProblem" id="sProblem" value="0">
		<input type="hidden" name="nEarning" id="nEarning" value="0">
		
		<input type="hidden" name="nIDCurrentPPP" id="nIDCurrentPPP" value="0">
		<input type="hidden" name="nIDElement" id="nIDElement" value="0">
		<input type="hidden" name="nIDLimitCard" id="nIDLimitCard" value="{$nIDLimitCard}">
		<input type="hidden" name="nIDObject" id="nIDObject" value="0">
		
		<input type="hidden" name="nPPPClosed" id="nPPPClosed" value="0">
		<input type="hidden" name="nLCClosed" id="nLCClosed" value="0">
		<input type="hidden" name="nLCCreateObject" id="nLCCreateObject" value="0">
		
		<table  cellspacing="0" cellpadding="0" width="100%" height="4%" id="filter" >
			<tr>
				<td>{include file=personal_card_tabs2.tpl}</td>
			</tr>
		</table>
		
		{if $nIDLimitCard}
			<table width="100%" border="0" class="input">
				<tr>
					<td>
						<input type="text" name="caption" id="caption" class="caption" value="ППП" readonly />
					</td>
					<td>&nbsp;</td>
					
					<td align="left" class="buttons">
						Номер на ППП:&nbsp;
						<input type="text" id="nIDPPP" name="nIDPPP" class="inp50" />
						&nbsp;
						<button onclick="processPPP();"><img src="images/plus.gif"> Нов </button>
					</td>
				</tr>
			</table>
			
			<hr />
			
			<table border="1" cellspacing="0" cellpadding="0" width="100%" id="tabcontainer">
				<tr>
					<td width="85%" valign="top" align="left">
						<table border="0" class="input">
							<tr>
								<td>
									<button onclick="setPPPElement( 0 );" style="width: 20px;"><img src="images/plus.gif"> Добави </button>
								</td>
								<td>
									<div id="sPPPDesc" name="sPPPDesc"></div>
								</td>
							</tr>
						</table>
						<div rpc_excel_panel="off" rpc_resize="off" id="result"></div>
					</td>
					<td width="15%" valign="top" align="right">
						<div id="ppplist" name="ppplist"></div>
					</td>
				</tr>
			</table>
		{/if}
	</form>
</body>

{literal}
	<script>
		if( $('nIDLimitCard').value != 0 )
		{
			$('id_person').value = parent.$('nID').value;
			loadXMLDoc2( 'result' );
		}
	</script>
{/literal}