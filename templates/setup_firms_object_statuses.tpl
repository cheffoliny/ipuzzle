{literal}
	<script>
		rpc_debug = true;
		
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
		
		function clearAll( sList )
		{
			oList = document.getElementById( sList );
			if( oList )
			{
				var i;
				for( i = oList.length - 1; i >= 0; i-- )
				{
					oList.remove( i );
				}
			}
		}
		
		function onStatus()
		{
			select_all_options( 'statuses_current' );
			
			rpc_on_exit = function()
			{
				rpc_on_exit = function() {}
				
				select_none( 'statuses_current' );
			}
			
			loadXMLDoc2( 'save' );
			return false;
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-dialog ui-configuration-dialog ui-status-mapping-dialog" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<div class="page_caption">Обобщена справка - Настройка на статуси</div>
	
	<center>
		<div class="ui-status-firm-filter">
			<table class="input ui-nomenclature-form">
				<tr>
					<td align="right">Фирма:&nbsp;</td>
					<td align="left">
						<select id="nIDFirm" name="nIDFirm" class="select200" onchange="clearAll( 'statuses_all' ); clearAll( 'statuses_current' ); loadXMLDoc2( 'result' );"/>
					</td>
				</tr>
			</table>
		</div>
	</center>
	

	<center>
		<fieldset class="ui-nomenclature-fieldset ui-status-transfer-wrap">
			<legend>Статуси</legend>
			<table class="input ui-nomenclature-transfer">
				<tr>
					<td align="center">
						<select name="statuses_all" id="statuses_all" style="width: 300px" size="10" ondblclick="move_option_to( 'statuses_all', 'statuses_current', 'right' );" multiple="multiple">
						</select>
					</td>
					<td align="center">
						<button id=b25 class="ui-nomenclature-transfer-button" name="button" title="Добави Статус" onClick="move_option_to( 'statuses_all', 'statuses_current', 'right' ); return false;"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button><br />
						<button id=b25 class="ui-nomenclature-transfer-button" name="button" title="Премахни Статус" onClick="move_option_to( 'statuses_all', 'statuses_current', 'left' ); return false;"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
					</td>
					<td align="center">
						<select name="statuses_current[]" id="statuses_current" style="width: 300px" size="10" ondblclick="move_option_to( 'statuses_all', 'statuses_current', 'left' );" multiple="multiple">
						</select>
					</td>
				</tr>
			</table>
		</fieldset>
	</center>

	<table class="ui-nomenclature-actions">
		<tr>
			<td align="right">
				<button id=b100 onclick="onStatus();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span>Потвърди</button>
			</td>
		</tr>
	</table>

</form>

<script>
	loadXMLDoc2( 'result' );
</script>
