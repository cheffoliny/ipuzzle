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

<form name="form1" id="form1" class="ui-object-core ui-object-store" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />


	{include file='object_tabs.tpl'}

	<div id="accordion" class="m-0 p-0 ui-object-store-accordion">

			<div class="nav nav-tabs navbar-dark bg-faded my-0" id="headingOne">
				<h5 class="my-0 px-1">
					<button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						<span class="ui-icon ui-icon-expand" aria-hidden="true"></span> Наличност в обекта
					</button>
				</h5>
{*				<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();">*}
			</div>

			<div id="collapseOne" class="collapse show p-0 m-0" aria-labelledby="headingOne" data-parent="#accordion">
				<iframe class="w-100 h-100 p-0 m-0 ui-object-store-frame" id="object_state" name="object_state" frameborder="0" src="page.php?page=object_store_state" title="Наличност в обекта"></iframe>
			</div>

			<div class="nav nav-tabs navbar-dark bg-faded mb-1" id="headingTwo">
				<h5 class="mb-0">
					<button type="button" class="btn btn-link collapsed float-left" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
						<span class="ui-icon ui-icon-expand" aria-hidden="true"></span> ППП
					</button>
				</h5>
			</div>
			<div id="collapseTwo" class="collapse p-0 m-0" aria-labelledby="headingTwo" data-parent="#accordion">
				<iframe class="w-100 h-100 p-0 m-0 ui-object-store-frame" id="object_ppp" name="object_ppp" frameborder="0" src="page.php?page=object_store_ppp" title="Приемо-предавателни протоколи"></iframe>
			</div>

	</div>

	
<div id="search" class="navbar fixed-bottom flex-row navbar-expand-lg ui-object-actions ui-object-store-actions">
	<div class="col ">
		<button type="button" onclick="openPPPForTransfer( 0, 1 );" class="btn btn-sm btn-success"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Към обекта </button>
		<button type="button" onclick="openPPPForTransfer( 0, 2 );" class="btn btn-sm btn-danger"><span class="ui-icon ui-icon-minus" aria-hidden="true"></span> От обекта </button>
	</div>
	<div class="col text-right py-2">
		<button type="button" id="b100" class="btn btn-sm btn-danger" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
	</div>
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
