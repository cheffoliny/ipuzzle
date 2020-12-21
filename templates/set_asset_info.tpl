{literal}
 <script>
 	rpc_debug = true;
	function SubmitForm() {
		loadXMLDoc2('save', 0);
		rpc_on_exit = function() {
			if ( typeof(window.opener.test) != 'undefined' ) {
				window.opener.test();	
			}
			window.opener.location.reload();
			rpc_on_exit = function() {};
			parent.window.close();
		}
	}
	
 </script>
{/literal}

<div class="content">
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}">
		
		<div class="page_caption">Редакция на<br>Амортизационен период</div>
		<br>

		<fieldset>
		 <table class="input">
				<tr class="even">
					<td>Амортизационен период:</td>
					<td>
						<input type="text" name="amort_period" id="amort_period" class="input100" onkeypress="return formatDigits(event)"/>&nbsp;
					</td>
				</tr>
		 </table>
		</fieldset>
		<div style="height: 10px;"></div>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" class="search" onclick="return SubmitForm();"> Запиши </button>
					<button onclick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

{literal}
<script>
	loadXMLDoc2('load');
</script>
{/literal}