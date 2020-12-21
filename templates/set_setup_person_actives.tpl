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


<form name="form1" id="form1" onsubmit="return false;">
<input type="hidden" id="id" name="id" value="{$id|default:0}" />
<input type="hidden" id="id_person" name="id_person" value="{$id_person|default:0}" />

<div class="page_caption">ППП - зачисляване</div>

<table cellspacing="0" cellpadding="0" width="100%" id="filter" >

<tr>
	<td id="filter_result">
	<!-- начало на работната част -->
	<center>
		<table class="search">
			<tr>
				<td valign="top" align="right" width="690px">
					<button id="b100" onClick="setPPP(0);"><img src="images/plus.gif" />Добави</button>
				</td>
			</tr>

	  </table>
	</center>

	<hr>
	
	<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width:700px; height:330px;overflow: auto;"></div>

 	<!-- край на работната част -->
	</td>
</tr>
</table>


<div id="search"  style="padding-top:10px;width:700px;">
	<table width="100%" cellspacing=1px>
		<tr valign="top">
			<td valign="top" align="right" width="700px">
				<button id="b100" onClick="parent.window.close();" ><img src="images/cancel.gif" />Затвори</button>
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