<script>
{literal}
	rpc_debug = true;
	
	function onInit()
	{
		
		rpc_on_exit = function()
		{
			if( $('sParent').value != 0)
			{
				//$('nIsCtrl').disabled = "disabled";
			}			
			rpc_on_exit = function() {}
		}
		loadXMLDoc2( 'load' );
		
	}
	function onChangeIsCtrl()
	{		
		if(	$('nID').value == 0 && $('sParent').value != 0 )
		{
			$('IsCtrl').style.display = "none";
		}
		if( $('nID').value == 0 && $('sParent').value == 0 )
		{
			$('IsCtrl').style.display = "block";
		}
	}

	function saveForm() {
		loadXMLDoc2('save',3);
	}
	
{/literal}
</script>
<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-configuration-dialog ui-nomenclature-type-dialog" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">

	<div class="page_caption" id="head_window">{if $nID}Редакция на{else}Нов{/if} тип номенклатура</div>
	
	<table class="search ui-nomenclature-form ui-nomenclature-type-fields" style="width: 100%;">
		<tr>		
			<td style="width: 200px; padding: 7px;">
				
			<div class="input-group" style="margin: 2px;">			
				<span class="input-group-addon"><span class="ui-icon ui-icon-tag" aria-hidden="true"></span></span>
				<input type="text" name="sName" id="sName" class="inp250" placeholder="Наименование на типа..."/>
			</div>
			
			<div class="input-group" style="margin: 2px;">			
				<span class="input-group-addon"><span class="ui-icon ui-icon-tag" aria-hidden="true"></span></span>
				<select name="sParent" id="sParent" class="select250" onchange="onChangeIsCtrl()" ></select>
			</div>
		
			<div class="input-group" style="margin: 2px;" id="IsCtrl">
				<input type="checkbox" name="nIsCtrl" id="nIsCtrl" class="clear ui-nomenclature-checkbox" >
				Контролен панел
			</div>
				
			</td>
		</tr>
	</table>
	
	<br />
		
	<table class="page_data ui-nomenclature-actions">
		<tr>
			<td width="50">&nbsp;</td>
			<td style="text-align:right; padding: 5px 1px 5px 0;">
				<button onclick="saveForm();" class="btn btn-xs btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
				<button onClick="parent.window.close();" class="btn btn-xs btn-danger"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
			</td>
		</tr>
	</table>
	
		
</form>

<script>	
	onInit();
</script>
