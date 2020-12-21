{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function update()
		{
			select_all_options( 'nomenclatures_current' );
			
			rpc_on_exit = function ( nCode )
			{
				if( !parseInt( nCode ) )
				{
					if( opener.parent.document.getElementById('operations_scheme') )
					{
						opener.parent.document.getElementById('operations_scheme').src = 'page.php?page=tech_operations_scheme';
					}
				}
				
				rpc_on_exit = function () {}
			}
			
			loadXMLDoc2( 'save', 3 );
			return false;
		}
		
		function select_none( id )
		{
			oSEL = document.getElementById( id );
			if( oSEL.options.length )
			{
				for( i = 0; i < oSEL.options.length; i++ )oSEL.options[i].selected = false;
				return true;
			}
			else return false;
		}
	</script>
{/literal}

<div class="page_caption">{if $nID>0}Редактиране на Операция{else}Нова Операция{/if}</div>

<form id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID|default:0}">
	<input type="hidden" name="id" id="id" value="{$id|default:0}">
	
	<input type="hidden" name="fromList" id="fromList" value="">
	<input type="hidden" name="toList" id="toList" value="">
	
	<div id="builder">
		<table class="input">
			<tr>
				<td align="left">Наименование:</td>
				<td align="left">
					<input type="text" name="sName" id="sName" class="inp250" />
				</td>
			</tr>
			<tr>
				<td align="left">Цена труд:</td>
				<td align="left">
					<input type="text" name="nPrice" id="nPrice" class="inp100" onkeypress="return formatMoney(event);" />&nbsp; лв.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="checkbox" class="clear" name="nToContract" id="nToContract" />&nbsp; Използва се при изграждане на обект от ел. договор
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="checkbox" class="clear" name="nToArrange" id="nToArrange" />&nbsp; Използва се при изграждане/аранжиране
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="checkbox" class="clear" name="nCableOperation" id="nCableOperation" />&nbsp; Операция окабеляване
				</td>
			</tr>
			<tr>
				<td colspan=2 id=fieldset>
					<fieldset style="border: 1px solid black">
					<legend>Номенклатури</legend>
						<table>
							<tr>
								<td>
									<select name="nomenclatures_all" id="nomenclatures_all" style="width:300px" size="10" ondblclick="move_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' );" multiple="multiple">
									</select>
								</td>
								<td>
									<button id=b25 name="button" title="Добави Номенклатура" onClick="move_option_to( 'nomenclatures_all', 'nomenclatures_current', 'right' );"><img src=images/mright.gif /></button></br>
									<button id=b25 name="button" title="Премахни Номенклатура" onClick="move_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' );"><img src=images/mleft.gif /></button>
								</td>
								<td>
									<select name="nomenclatures_current[]" id="nomenclatures_current" style="width:300px" size="10" ondblclick="move_option_to( 'nomenclatures_all', 'nomenclatures_current', 'left' );" multiple="multiple">
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="search">
		<table width="100%" cellspacing=5px>
			<tr><td align="right" valign="bottom">
				<button id=b100 onclick="return update();"><img src=images/confirm.gif />Потвърди</button>&nbsp;
				<button id=b100 onClick="parent.window.close();"><img src="images/cancel.gif" />Затвори</button>
			</td></tr>
		</table>
	</div>
	
</form>

<script>
	 onInit();
</script>