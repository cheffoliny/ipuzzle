<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 )">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="sType" name="sType" value="{$sLatinType}">
		
		<div class="page_caption">Времетраене за тип обслужване "{$sType}"</div>
		<br />

		<table class="input">
			<tr class="odd">
				<td width="200">Времетраене:</td>
				<td width="50">
					<input type="text" name="nMinutes" id="nMinutes" class="inp50" onkeypress="return formatDigits(event);"/>
				</td>
				<td>мин.</td>
			</tr>
			<tr class="even">
				<td width="200"><div id="detector_timing_caption">Време за всеки следващ датчик:</div></td>
				<td width="50">
					<input type="text" name="nStepDetector" id="nStepDetector" class="inp50" onkeypress="return formatDigits(event);"/>
				</td>
				<td><div id="detector_timing_measure">мин.</div></td>
			</tr>
		</table>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

{literal}
	<script>
		var sType = $('sType').value;
		
		if( sType != "create" )
		{
			document.getElementById( 'nStepDetector' ).value = "";
			document.getElementById( 'nStepDetector' ).disabled = "disabled";
			
			document.getElementById( 'detector_timing_caption' ).style.color = "C8C8B4";
			document.getElementById( 'detector_timing_measure' ).style.color = "C8C8B4";
		}
		
		loadXMLDoc2( 'get' );
	</script>
{/literal}