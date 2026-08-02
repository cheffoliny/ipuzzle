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

<div class="content ui-object-link-shell">
	<form action="" name="form1" id="form1" class="ui-nomenclature-dialog ui-object-link-dialog" onSubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="id_object" name="id_object" value="">
		<input type="hidden" id="id_limit_card" name="id_limit_card" value="">
		
		
		<div class="page_caption">Обект към Електроннен договор № {$nNum}</div>
	
		<table class="ui-object-link-mode">
			<tr>
				<td>
					<button type="button" class="btn btn-sm btn-primary" onClick="newObject();"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нов Обект </button>
				</td>
				<td>
					<button type="button" class="btn btn-sm btn-light" onClick="existingObject();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Съществуващ </button>
				</td>
			</tr>
		</table>
		
		
		<div id="new_object" class="ui-object-link-panel">
			<fieldset class="ui-nomenclature-fieldset">
			<legend>Нов Обект</legend>
			<table class="input ui-nomenclature-form ui-object-link-fields">
				<tr class="odd">
					<td align="right">Номер:</td>
					<td>
						<input type="text" name="nNumNew" id="nNumNew" class="inp50" onkeypress="return formatDigits(event);" />
					</td>
					<td align="right">Сигнали:</td>
					<td>
						<select name="nIDTemplets" id="nIDTemplets" class="select150"></select>
					</td>
				</tr>
				<tr>
					<td align="right">Име:</td>
					<td colspan="3">
						<input type="text" name="sNameNew" id="sNameNew" class="ui-object-link-name" />
					</td>
				</tr>
			</table>
			</fieldset>
	
		
			<table class="input ui-nomenclature-actions ui-object-link-actions">
				<tr class="odd">
	
					<td style="text-align:right;">
						<button type="button" onClick="attachNewObject();" class="search"><span class="ui-icon ui-icon-check" aria-hidden="true"></span> Привържи </button>
						<button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
					</td>
				</tr>
			</table>
		</div>
		
		
		<div id="existing_object" class="ui-object-link-panel">
			<fieldset class="ui-nomenclature-fieldset">
			<legend>Съществуващ Обект</legend>
			<table class="input ui-nomenclature-form ui-object-link-fields">
				<tr class="odd">
					<td align="right">Номер:</td>
					<td>
						<input type="text" name="sNum" id="sNum" class="inp50" suggest="suggest" queryType="objByNum" onkeypress="return formatDigits(event);"/>
					</td>
				</tr>
				<tr>
					<td align="right">Име:</td>
					<td colspan="3">
						<input type="text" name="sName" id="sName" class="ui-object-link-name" suggest="suggest" queryType="objByName"/>
					</td>
				</tr>
			</table>
			</fieldset>
	
		
			<table class="input ui-nomenclature-actions ui-object-link-actions">
				<tr class="odd">
	
					<td style="text-align:right;">
						<button type="button" onClick="attachExistingObject();" class="search"><span class="ui-icon ui-icon-check" aria-hidden="true"></span> Привържи </button>
						<button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
					</td>
				</tr>
			</table>
		</div>
		
		
	</form>
</div>

<script>
	onInit();
</script>
