{literal}
	<script>
		rpc_debug=true;

		var my_action = '';
	</script>
{/literal}
<form action="" method="POST" id="form1" name="form1" class="ui-personnel-dialog ui-leave-balance-dialog" onsubmit="my_action = 'save'; return loadXMLDoc( 'save', 2 );">
	<input type="hidden" id="id" name="id" value="{$id}">
	<input type="hidden" id="id_person" name="id_person" value="{$id_person}">

	<div class="modal-content pb-3">
		<div class="modal-header">
			{if $id}Редактиране на отпуска{else}Нова отпуска{/if}
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">

			<div class="row mb-1">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="ui-icon ui-icon-calendar" aria-hidden="true" title="Година..."></span>
						</div>
						<input class="form-control" id="year" name="year" type="text" value="{if !$id}{$year}{/if}" maxlength="4" onkeypress="return formatDigits(event);" /> Год.
					</div>
				</div>
			</div>
			<div class="row mb-1">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="ui-icon ui-icon-calendar" aria-hidden="true" title="Дни..."></span>
						</div>
						<input class="form-control" id="due_days" name="due_days" type="text" maxlength="2" onkeypress="return formatDigits(event);" /> Дни
					</div>
				</div>
			</div>
			<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
				<div class="col-12 col-sm-12 col-lg-12">
					<div class="input-group input-group-sm text-right">
						<button class="btn btn-block btn-sm btn-primary" type="submit"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
					</div>
				</div>
			</nav>
		</div>
	</div>
</form>
{literal}
	<script>
		loadXMLDoc('result');
		
		rpc_on_exit = function( err ) {
			if( my_action == 'save' && err == 0 ) {
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
	</script>
{/literal}
