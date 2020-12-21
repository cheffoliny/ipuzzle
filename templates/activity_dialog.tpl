{literal}
<script>
	rpc_debug = true;
	
		function IsEmpty( id, message )  
		{
			aTextField = $(id);
			
			if ( ( aTextField.value.length==0 ) || ( aTextField.value==null ) ) 
			{
				aTextField.focus();
				alert( message);
				return true;
			} 
			else 
			{ 
				return false; 
			}
		}
		
		function IsTooLong( id, length, message )
		{
			aTextField = $(id);
			
			if ( aTextField.value.length > length )
			{
				alert( message );
				aTextField.focus();
				aTextField.selected = "selected";
				return true;				
			}
			else
			{
				return false;
			}
		}
		
		function submit_form() 
		{
			if ( !IsEmpty( 'sName', 'Моля, въведете "Наименование"!' ) && !IsTooLong( 'sDesc', 128, 'Моля, въведете описание по-кратко от 128 символа' ) ) 
			{ 
				loadXMLDoc2( 'save', 3 );	
			} 
		}	
	
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save' ); return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} дейност</div>
		<br />

		<table class="input">
		
			<tr class="odd">
				<td width="200">Име:</td>
				<td>
					<input type="text" name="sName" id="sName" class="inp250"  maxlength="30" />
				</td>
			</tr>
			<tr class="even">
				<td width="200">Описание:</td>
				<td>
					<textarea id="sDesc" name="sDesc" rows="4" cols="33"></textarea>
				</td>
			</tr>
			
		</table>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button onClick="submit_form();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>



{literal}
<script>

	loadXMLDoc2( 'init' );

</script>
{/literal}|