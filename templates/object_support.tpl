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

	{include file='object_tabs.tpl'}

{*<table class="search" style="width:100%;">*}

{*	<tr>*}
{*		<td id="filter_result">*}
{*	<!-- начало на работната част -->*}
{*	*}
{*	<table class="search">*}
{*		<tr>*}
{*			<td style="width: 20px;">*}
{*				<input type="checkbox" id="nService" name="nService" class="clear" onClick="load();" />*}
{*			</td>*}
{*			<td style="width: 780px; text-align: left;">Неизпълнени</td>*}
{*		</tr>*}

{*  </table>*}

{*	<hr>*}
{*	*}
{*{if $mobile}*}
	{*{if $cnt>6}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">*}
	{*{else}*}
		{*<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">*}
	{*{/if}*}
{*{/if}*}
	
	<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" class="w-100 h-100" style="overflow-x: auto; overflow-y: auto; !important"></div>

	</div>
 	<!-- край на работната част -->
	</td>
</tr>
</table>

	<div id="search" class="navbar fixed-bottom flex-row navbar-expand-lg">
		<div class="col ">
		</div>
		<div class="col text-right py-2">
			<button id="b100" class="btn btn-sm btn-danger" onClick="window.close();"><i class="fa fa-times"></i> Затвори </button>
		</div>
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
