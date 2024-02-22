{literal}
	<script type="text/javascript">
		rpc_debug = true;
		var onSubmit = function() {
			loadXMLDoc2('result');
		}
		var onClickOpenUknown = function(ids) {
			if (!ids) return;
			var id_person = ids.split('@')[0];
			var id_office = ids.split('@')[1];
			dialog_win('objects_traning_window&id_person='+id_person+'&id_office='+id_office+'&type=unknown',1000,600);
		}
		var onClickOpenKnown = function(ids) {
			if (!ids) return;
			var id_person = ids.split('@')[0];
			var id_office = ids.split('@')[1];
			dialog_win('objects_traning_window&id_person='+id_person+'&id_office='+id_office+'&type=familiar',1000,600);
		}
		var onClickOpenVisited = function(ids) {
			if (!ids) return;
			var id_person = ids.split('@')[0];
			var id_office = ids.split('@')[1];
			dialog_win('objects_traning_window&id_person='+id_person+'&id_office='+id_office+'&type=visited',1000,600);
		}
		var onClickOpenReacted = function(ids) {
			if (!ids) return;
			var id_person = ids.split('@')[0];
			var id_office = ids.split('@')[1];
			dialog_win('objects_traning_window&id_person='+id_person+'&id_office='+id_office+'&type=reacted',1000,600);
		}
	</script>
{/literal}
<form name="form1" id="form1" onsubmit="onSubmit();return false;" overflow>
	<input type="hidden" id="office" name="office" value="{$office}" />
	<input type="hidden" id="officeName" name="officeName" value="{$officeName}" />

	<ul class="nav nav-tabs nav-intelli">
		<li class="nav-item text-center" title="Мониторинг"><a class="nav-link inactive" href="#">Обекти обучение</a></li>
	</ul>

	<div>
		<div class="row justify-content-start pl-3 pb-1 pt-2 table-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-handshake fa-fw" data-fa-transform="right-22 down-10" title="Тип на контрагента..."></span>
					</div>
					<select class="form-control"  id="nIDOffice" name="nIDOffice">
                        {foreach from=$aOffices item=item}
                            <option value="{$item.id}">{$item.name|escape:"html"}</option>
                        {/foreach}
                    </select>
                </div>
			</div>

			<div class="col-6 col-sm-8 col-lg-4 pl-3">
				<div class="input-group input-group-sm">
					<button type="button" id="btnSubmit" type="submit" onclick="onSubmit(); return false;" class="btn btn-primary btn-sm"><i class="far fa-search"></i> Покажи</button>
				</div>
			</div>
		</div>
	</div>

	<div id="result"></div>	
</form>
<script>loadXMLDoc2('load');</script>