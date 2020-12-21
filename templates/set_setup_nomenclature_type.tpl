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
<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
		
	<table class="search" style="width: 100%;">
		<tr>
			<td colspan="2" class="header_buttons" style="height: 33px;">
			<span id="head_window">{if $nID}Редакция на{else}Нов{/if} тип номенклатура</span> 
			</td>
		</tr>
	
		<tr>		
			<td style="width: 200px; padding: 7px;">
				
			<div class="input-group" style="margin: 2px;">			
				<span class="input-group-addon"><img src="images/glyphicons/typenom.png" style="width: 12px; height: 12px;"></span>
				<input type="text" name="sName" id="sName" class="inp250" placeholder="Наименование на типа..."/>
			</div>
			
			<div class="input-group" style="margin: 2px;">			
				<span class="input-group-addon"><img src="images/glyphicons/typenom.png" style="width: 12px; height: 12px;"></span>
				<select name="sParent" id="sParent" class="select250" onchange="onChangeIsCtrl()" ></select>
			</div>
		
			<div class="input-group" style="margin: 2px;" id="IsCtrl">
				<input type="checkbox" name="nIsCtrl" id="nIsCtrl" class="clear" >
				Контролен панел
			</div>
				
			</td>
		</tr>
	</table>
	
	<br />
		
	<table class="page_data">
		<tr>
			<td width="50">&nbsp;</td>
			<td style="text-align:right; padding: 5px 1px 5px 0;">
				<button onclick="saveForm();" class="btn btn-xs btn-success"><img src="images/glyphicons/save.png" style="width: 14px; height: 14px;"> Запиши </button>
				<button onClick="parent.window.close();" class="btn btn-xs btn-danger"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
			</td>
		</tr>
	</table>
	
		
</form>

<script>	
	onInit();
</script>