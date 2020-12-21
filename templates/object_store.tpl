{literal}
	<script>
		rpc_debug = true;
		
		function techSupport() {
			var id = $('nID').value;
				
			dialogTechSupport(id);
		}	

		function openPPPForTransfer( id, setstorage )
		{
			var params = 'id=' + id;
			params += '&id_object=' + $('nID').value;
			params += '&setstorage=' + setstorage;
			
			dialogPPP2( params );
		}
			
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	
<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons">
		<span id="head_window">Техника на {$object}</span> 
				<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
				{include file=object_tabs.tpl}
			</td>
		</tr>
		
		<tr class="odd">
			<td id="filter_result">
		<!-- начало на работната част -->
		
		
		{*{if $mobile}*}
			{*{if $cnt > 6}*}
				{*<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto;">*}
			{*{else}*}
				{*<div id="search" style="padding-top: 10px; width: 800px; height: 270px; overflow-y: auto;">*}
			{*{/if}*}
		{*{/if}*}
		
		<table border='0' width="100%" height="410px">
			<tr>
				<td width="100%" height="50%">
					<iframe id="object_state" name="object_state" width="100%" height="100%" frameborder=0 src='page.php?page=object_store_state'>
					</iframe>
				</td>
			</tr>
			<tr>
				<td width="100%" height="50%">
					<iframe id="object_ppp" name="object_ppp" width="100%" height="100%" frameborder=0 src='page.php?page=object_store_ppp'>
					</iframe>
				</td>
			</tr>
		</table>
		
		{*{if $mobile}</div>{/if}*}
	 	<!-- край на работната част -->
		</td>
	</tr>
</table>
	
<div id="search"  style="padding-top:10px; width:800px;">
	<table class="page_data" >
		<tr>
			<td style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
				<button onclick="openPPPForTransfer( 0, 1 );" class="btn btn-xs btn-success"><i class="fa fa-plus"></i> Към обекта </button>
				&nbsp;
				<button onclick="openPPPForTransfer( 0, 2 );" class="btn btn-xs btn-danger"><img src="images/glyphicons/minus.png"> От обекта </button>
			</td>
			<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
			</td>
		</tr>
	</table>
</div>
	
	<div id="NoDisplay" style="display: none;"></div>
</form>

<script>
	{if !$edit.object_store_edit}
		{literal}
			if( form=document.getElementById( 'form1' ) )
			{
				for( i = 0; i < form.elements.length - 1; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
			}
		{/literal}
	{/if}
</script>