{literal}
	<script>
		rpc_debug = true;
		
		InitSuggestForm = function() {
			for(var i=0; i<suggest_elements.length; i++) {
				switch( suggest_elements[i]['id'] ) {
					case 'sNum':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
					case 'sName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
						break;
				}
			}
		}
		
		function onSuggestObject( aParams ) {
			var aParts = aParams.KEY.split(';');
			
			$('id_object').value = 	aParts[0];
			$('sNum').value = 		aParts[1];
			$('sName').value =		aParts[2];
		}
		
		function onInit() {
			attachEventListener( $('sNum'),  "keypress", onKeyPressObjectNum);
			attachEventListener( $('sName'), "keypress", onKeyPressObjectName);
			
			$('new_object').style.display = "none";
			$('existing_object').style.display = "none";
			
			loadXMLDoc2('load');
		}
		
		function onKeyPressObjectNum() {
			$('id_object').value = "";
			$('sName').value = "";
		}
		
		function onKeyPressObjectName() {
			$('id_object').value = "";
			$('sNum').value = "";
		}
		
		function newObject() {
			$('new_object').style.display = "block";
			$('existing_object').style.display = "none";
		}
		
		function existingObject() {
			$('existing_object').style.display = "block";
			$('new_object').style.display = "none";
		}

		function attachNewObject() {
			loadXMLDoc2('attachNewObject');
			
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					//alert($('id_limit_card').value);
					dialogLimitCard($('id_limit_card').value);
				}	
			}	
		}
		
		function attachExistingObject() {
			loadXMLDoc2('attachExistingObject',2);
			
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					dialogLimitCard($('id_limit_card').value);
				}		
			}	
		}
		
	</script>
{/literal}

<div class="content">
	<form action="" name="form1" id="form1" onSubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="id_object" name="id_object" value="">
		<input type="hidden" id="id_limit_card" name="id_limit_card" value="">
		
		
		<div class="page_caption">Обект към Електроннен договор № {$nNum}</div>
	
		<table>
			<tr>
				<td>
					<button onClick="newObject();"> Нов Обект </button>
				</td>
				<td>
					<button onClick="existingObject();"> Съществуващ </button>
				</td>
			</tr>
		</table>
		
		
		<div id="new_object">
			<fieldset>
			<legend>Нов Обект</legend>
			<table class="input">
				<tr class="odd">
					<td align="right">Номер:</td>
					<td>
						<input type="text" name="nNumNew" id="nNumNew" class="inp50" onkeypress="return formatDigits(event);" />
					</td>
					<td align="right">Сигнали:</td>
					<td>
						<select name="nIDTemplets" id="nIDTemplets" class="select150" />
					</td>
				</tr>
				<tr>
					<td align="right">Име:</td>
					<td colspan="3">
						<input type="text" name="sNameNew" id="sNameNew" style="width:270px;"/>
					</td>
				</tr>
			</table>
			</fieldset>
	
		
			<table class="input">
				<tr class="odd">
	
					<td style="text-align:right;">
						<button onClick="attachNewObject();" class="search"> Привържи </button>
						<button onClick="parent.window.close();"> Затвори </button>
					</td>
				</tr>
			</table>
		</div>
		
		
		<div id="existing_object">
			<fieldset>
			<legend>Съществуващ Обект</legend>
			<table class="input">
				<tr class="odd">
					<td align="right">Номер:</td>
					<td>
						<input type="text" name="sNum" id="sNum" class="inp50" suggest="suggest" queryType="objByNum" onkeypress="return formatDigits(event);"/>
					</td>
				</tr>
				<tr>
					<td align="right">Име:</td>
					<td colspan="3">
						<input type="text" name="sName" id="sName" style="width:270px;"  suggest="suggest" queryType="objByName"/>
					</td>
				</tr>
			</table>
			</fieldset>
	
		
			<table class="input">
				<tr class="odd">
	
					<td style="text-align:right;">
						<button onClick="attachExistingObject();" class="search"> Привържи </button>
						<button onClick="parent.window.close();"> Затвори </button>
					</td>
				</tr>
			</table>
		</div>
		
		
	</form>
</div>

<script>
	onInit();
</script>