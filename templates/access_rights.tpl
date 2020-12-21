{literal}
	<script>
		rpc_debug = true;
		rpc_method = "post";
		
		function changeLevel( id )
		{
			var nMode = 0;
			
			var nIDToChange = "";
			var sLevel = "";
			var nValue = document.getElementById( id ).value;
			
			if( nValue == 0 )nValue = 1;
			else nValue = 0;
			
			document.getElementById( id ).value = nValue;
			
			for( i = 0; i < id.length; i++ )
			{
				if( id.charAt( i ) == "[" )nMode = 1;
				else if( id.charAt( i ) == "]" )nMode = 0;
				else
				{
					if( nMode == 0 )sLevel += id.charAt( i );
					if( nMode == 1 )nIDToChange += id.charAt( i );
				}
			}
			
			//Get Records
			var Records = new Array();
			if( $("sMadeChanges").value != "" )
			{
				Records = $("sMadeChanges").value.split( ";" );
				for( q = 0; q < Records.length; q++ )
				{
					var Elements = new Array();
					Elements = Records[q].split( "," );
					if( Elements[0] == nIDToChange && Elements[1] == sLevel )
					{
						Records.splice( q, 1 );
					}
				}
				var NewElements = nIDToChange + "," + sLevel + "," + nValue;
				Records.push( NewElements );
				$("sMadeChanges").value = Records.join( ";" );
			}
			else
			{
				var NewElements = nIDToChange + "," + sLevel + "," + nValue;
				Records.push( NewElements );
				$("sMadeChanges").value = Records.join( ";" );
			}
			//End Get Records
		}
		
		function saveLevels()
		{
			if( $("sMadeChanges").value != "" )
			{
				if( confirm( "Запазване на промените?" ) )
				{
					loadXMLDoc2( "changeLevels" );
				}
			}
		}
		
		function setupAccount( id )
		{
			dialogSetupAccount2( "id=" + id + "&selall=1" );
		}
		
		function setupProfile( id )
		{
			dialog_win( 'set_setup_access_profile&id=' + id + "&selall=1", 750, 580, 0, 'set_setup_access_profile' );
		}
		
		function unselect_all_options( id )
		{
			oSEL = document.getElementById( id );
			
			if( oSEL.options.length )
			{
				for( i = 0; i < oSEL.options.length; i++ )oSEL.options[i].selected = false;
				return true;
			}
			else return false;
		}
		
		function count_all_options( id )
		{
			var oSEL = document.getElementById( id );
			
			if( oSEL.options.length )
			{
				return oSEL.options.length
			}
			else return 0;
		}
		
		function processResult()
		{
			var count = count_all_options( 'search_rights' );
			if( count == 0 )
			{
				alert( "Моля, изберете поне едно поле в \"Изобразени права\"!" );
				return false;
			}
			
			select_all_options( 'search_rights' );
			
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					unselect_all_options( 'search_rights' );
				}
				
				rpc_on_exit = function( nCode ) {}
			}
			
			showFilters();
			loadXMLDoc2( 'result' );
			
			return true;
		}
		
		function clickPerson()
		{
			if( document.getElementById( 'nByPerson' ).checked == true )
			{
				document.getElementById( 'firmregion' ).style.display = "block";
				document.getElementById( 'allprofiles' ).style.display = "none";
				document.getElementById( 'saveButton' ).style.display = "none";
				document.getElementById( 'nByProfile' ).checked = false;
			}
			else
			{
				document.getElementById( 'firmregion' ).style.display = "none";
				document.getElementById( 'allprofiles' ).style.display = "block";
				document.getElementById( 'saveButton' ).style.display = "block";
				document.getElementById( 'nByProfile' ).checked = true;
			}
		}
		
		function clickProfile()
		{
			if( document.getElementById( 'nByProfile' ).checked == true )
			{
				document.getElementById( 'firmregion' ).style.display = "none";
				document.getElementById( 'allprofiles' ).style.display = "block";
				document.getElementById( 'saveButton' ).style.display = "block";
				document.getElementById( 'nByPerson' ).checked = false;
			}
			else
			{
				document.getElementById( 'firmregion' ).style.display = "block";
				document.getElementById( 'allprofiles' ).style.display = "none";
				document.getElementById( 'saveButton' ).style.display = "none";
				document.getElementById( 'nByPerson' ).checked = true;
			}
		}
		
		function showFilters()
		{
			var TheStyle = document.getElementById( 'filters' ).style.display;
			
			if( TheStyle == 'none' )document.getElementById( 'filters' ).style.display = 'block';
			else document.getElementById( 'filters' ).style.display = 'none';
			
			if( typeof( xslResizer ) == 'function' )
			{
				xslResizer();
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="sResultType" name="sResultType" value="">			<!-- Типа на последно-генерирания резултат -->
	<input type="hidden" id="sMadeChanges" name="sMadeChanges" value="">		<!-- Направени до момента промени в правата -->
	
	<div class="page_caption">Права на Достъп</div>
	
	<table width="100%">
		<tr>
			<td align="right">
				<button type="button" style="width: 30px;" onClick="showFilters();"><img src="images/search2.gif"></button>
			</td>
		</tr>
	</table>
	
	<div id="filters">
		<center>
			<table border="0" width="100%">
				<tr>
					<td align="right">
				  		<table class="search" cellspacing="3">
							<tr>
								<td valign="top" align="center">
									<fieldset style="width: 750px;">
									<legend>Изобразени права:</legend>
										<table>
											<tr style="height: 5px;"><td colspan="3"></td></tr>
											<tr class="even">
												<td>
													<select name="all_rights" id="all_rights" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_rights', 'search_rights', 'right' );" multiple>
													</select>
												</td>
												<td>
													<button class="search" style="width: 50px;" name="button" title="Добави" onClick="move_option_to( 'all_rights', 'search_rights', 'right' ); return false;"><img src="images/mright.gif" /></button></br>
													<button name="button" style="width: 50px;" title="Премахни" onClick="move_option_to( 'all_rights', 'search_rights', 'left' ); return false;"><img src="images/mleft.gif" /></button>
												</td>
												<td>
													<select name="search_rights[]" id="search_rights" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_rights', 'search_rights', 'left' );" multiple>
													</select>
												</td>
											</tr>
											<tr style="height: 5px;"><td colspan="3"></td></tr>
										</table>
									</fieldset>
								</td>
							</tr>
						</table>
					</td>
					<td>&nbsp;&nbsp;</td>
					<td valign="top" align="left">
						<table border="0" width="350px">
							<tr>
								<td>&nbsp;</td>
							</tr>
						</table>
						
						<table class="search">
							<tr>
								<td>
									<input type="radio" id="nByPerson" name="nByPerson" class="clear" onclick="clickPerson();" checked="checked" />&nbsp;По Служители
								</td>
								<td>
									<input type="radio" id="nByProfile" name="nByProfile" class="clear" onclick="clickProfile();" />&nbsp;По Профили
								</td>
							</tr>
						</table>
						
						<div id="firmregion">
							<table class="search">
								<tr>
									<td align="right">Фирма:&nbsp;</td>
									<td>
										<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'getOffices' )" />
									</td>
								</tr>
								<tr>
									<td align="right">Регион:&nbsp;</td>
									<td>
										<select class="default" name="nIDOffice" id="nIDOffice" />
									</td>
								</tr>
								<tr>
									<td align="right">Длъжност:&nbsp;</td>
									<td>
										<select class="default" name="nIDPosition" id="nIDPosition" />
									</td>
								</tr>
						  	</table>
					  	</div>
					  	
					  	<div id="allprofiles" style="display: none;">
					  		<table class="search">
					  			<tr>
					  				<td>&nbsp;</td>
					  			</tr>
					  			<tr>
					  				<td>
					  					Всички профили:&nbsp;<input type="checkbox" id="nAllProfiles" name="nAllProfiles" class="clear" />
					  				</td>
					  			</tr>
					  		</table>
					  	</div>
					  	
					  	<br />
					  	
					  	{if $right_edit}
						<div id="saveButton" style="display: none;"><button name="Button" onclick="saveLevels();"><img src="images/confirm.gif"> Запази Промените </button></div>
						{/if}
					  	<button name="Button" onclick="processResult();"><img src="images/confirm.gif"> Търси </button>
					</td>
				</tr>
			</table>
		</center>
	</div>
	
	<hr>
	
	<div id="result" rpc_excel_panel="off"></div>
</form>

<script>
	loadXMLDoc2( 'setFields' );
</script>