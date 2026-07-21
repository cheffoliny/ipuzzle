{literal}
	<script>
		rpc_debug = true;
		
		function submit_form() {
			loadXMLDoc( 'save', 0 );
		}
		
		function setPPP(id) {
			var person = document.getElementById('id_person').value;
			dialogPPP( id, person );
		}
	</script>
{/literal}


<form name="form1" id="form1" class="ui-nomenclature-dialog ui-contract-dialog ui-person-assets-attach" onsubmit="return false;">
<input type="hidden" id="id" name="id" value="{$id|default:0}" />
<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}" />

<div class="page_caption">ППП - зачисляване</div>

<table cellspacing="0" cellpadding="0" width="100%" id="filter" >

<tr>
	<td id="filter_result">
	<!-- начало на работната част -->
	<center>
		<table class="search ui-contract-toolbar">
			<tr>
				<td valign="top" align="right" width="690px">
					<button type="button" id="b100" class="search" onClick="setPPP(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span>Добави</button>
				</td>
			</tr>

	  </table>
	</center>

	<hr>
	
	<div id="result" class="ui-contract-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width:700px; height:330px;overflow: auto;"></div>

 	<!-- край на работната част -->
	</td>
</tr>
</table>


<div id="search" class="ui-contract-actions-wrap" style="padding-top:10px;width:700px;">
	<table class="ui-nomenclature-actions ui-contract-actions" width="100%" cellspacing=1px>
		<tr valign="top">
			<td valign="top" align="right" width="700px">
				<button type="button" id="b100" class="btn btn-xs btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
			</td>
		</tr>
	</table>
</div>
<div id="NoDisplay" style="display:none"></div>
</form>

<script>
	loadXMLDoc('result');
	//loadMainData();</script>
	{if !$edit_personnel}
		if( form=document.getElementById('form1') )
		//	for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	{/if}
</script>
