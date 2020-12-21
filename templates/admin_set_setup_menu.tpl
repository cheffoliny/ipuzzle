{literal}
	<script>
		rpc_debug = true;

		function onInit() {
			loadXMLDoc2('load');
		}

		function saveForm() {
			loadXMLDoc2('save',3);
		}

	</script>
{/literal}

<form id="form1" name="form1" onSubmit="return loadXMLDoc('update', 3);">
	<input type=hidden name="id" value="{$id|default:0}">

	<div class="modal-content pb-3">
		<div class="modal-header">
			<h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция{else}Добавяне{/if} на меню</h6>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="far fa-user fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
						</div>
						<input class="form-control" type="text" name="title" id="title" value="{$title|escape:"html"}" placeholder="Име..." />
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="far fa-list-alt fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
						</div>
						<input class="form-control" name="filename" id="filename" value="{$filename|escape:"html"}" placeholder="Файл..." />
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="far fa-folder-open fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></span>
						</div>
						<select class="form-control" name="parent_id" id="parent_id" onchange="loadXMLDoc( 'after' );" >
							<option value="0"> -- Подчинен -- </option>
						{foreach from=$menu_elements item=item}
							<option value="{$item.id}"{if $item.id eq parent_id} selected{/if}>{$item.title|escape:"html"}
						{/foreach}
						</select>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="far fa-folder-tree fa-fw" data-fa-transform="right-22 down-10" title="Име на контрагент"></i>
						</div>
						<select class="form-control" name="menu_order" id="menu_order">
							<option value="0"> -- В началото -- </option>
							{foreach from=$menu_elements item=item}
								<option value="{$item.menu_order}"{if $item.menu_order eq $prev_order} selected{/if}>{$item.title|escape:"html"}
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-footer mb-0">
			<button class="btn btn-sm btn-block btn-primary" ><i class="fas fa-check"></i> Запиши </button>
		</div>

	</div>
</form>

<script>
	loadXMLDoc('result');	
</script>