{literal}
<script>
	rpc_debug = true;

	function editLimitCard(id) {
		dialogLimitCard(id);
	}

	function load() {
		loadXMLDoc2('result');
	}
		
	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}	
</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
<input type="hidden" id="nIDSupport" name="nIDSupport" value="0" />

<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons">
		<span id="head_window">Техническа дейност за {$object}</span> 
		<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
		{include file=object_tabs.tpl}
		</td>
	</tr>
	<tr>
		<td id="filter_result">
	<!-- начало на работната част -->
	
	<table class="search">
		<tr>
			<td style="width: 20px;">
				<input type="checkbox" id="nService" name="nService" class="clear" onClick="load();" />
			</td>
			<td style="width: 780px; text-align: left;">Неизпълнени</td>
		</tr>

  </table>

	<hr>
	
{*{if $mobile}*}
	{*{if $cnt>6}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">*}
	{*{else}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">*}
	{*{/if}*}
{*{/if}*}
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="padding: 1px; width: 100%; height: 360px; overflow-x: auto; overflow-y: auto; !important"></div>

	</div>
 	<!-- край на работната част -->
	</td>
</tr>
</table>

<div id="search"  style="padding-top:10px;width:800px;">
	<table class="page_data" >
		<tr valign="top">
			<td valign="top" style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
				&nbsp;
			</td>
			<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
			</td>
		</tr>
	</table>
</div>


<div id="NoDisplay" style="display:none"></div>
</form>


<script>
	loadXMLDoc2('result');
	
	{if !$edit.object_support_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
