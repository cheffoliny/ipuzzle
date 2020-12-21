{literal}
<script type="text/javascript">
	rpc_debug = true;

	rpc_on_exit = function () {

		jQuery('[data-provider]').each(function (i, el) {

			var aNum = el.innerHTML.split(','),
					img = parseInt(el.dataset.provider, 10) === 0 ? 'coins.gif' : el.dataset.provider.toLowerCase() + '.png',
					container = '';

			aNum.forEach(function (doc, cnt, arr) {
				container += '<span data-doc="'+ doc +'" class="pay-icon"><img src="images/'+ img +'" /></span>';
			});

			jQuery(el).html(jQuery(container));
		});

	};

	jQuery(document).ready(function () {
		jQuery('#result')
			.on('click', '.pay-icon', function () {
				dialogSale2(this.dataset.doc.toString());
			})
			.on('click', '[data-client]', function () {
				var cl_id = this.dataset.client;
					dialogClientInfo( cl_id );
			});
	});

	function go_to_page( page ) {
		obj = document.getElementById( page );
		obj.href = "page.php?page=" + page ;
		return true;
	}
</script>

<style type="text/css">
	span.pay-icon {
		padding-right: 5px;
		cursor: pointer;
	}
	[data-client] {
		text-decoration: underline;
		color: #428bca;
	}
	[data-client]:hover {
		cursor: pointer;
	}
</style>
{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="editToDate" 		input_element_id="sToDate" 		tool_tip="Изберете дата"></dlcalendar>
<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id_param" id="id_param" value="0" />

	{include file="finance_operations_tabs.tpl"}

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">

			<table class="table-sm table-borderless ml-3">
				<tr>
				<td>
<!--					<div class="input-group">
						<span class="input-group-prepend" id="editFromDate" title="Изберете дата" style="cursor: pointer;"><i class="far fa-calendar-alt"></i></span>
						<input class="form-control inp75" type="text" name="sFromDate" id="sFromDate" onkeypress="return formatDate( event, '.' );" value="{$smarty.now|date_format:"%d.%m.%Y"}" />
						<span class="input-group-append"><i class="far fa-arrows-h"></i></span>
						<input class="form-control inp75" type="text" name="sToDate" id="sToDate" onkeypress="return formatDate( event, '.' );" value="{$smarty.now|date_format:"%d.%m.%Y"}" />
						<span class="input-group-append" id="editToDate" title="Изберете дата" style="cursor: pointer;"><i class="far fa-calendar-alt"></i></span>
					</div>-->
					<div class="input-group input-group-sm" title="Период на плащане">
						<div class="input-group-prepend">
							{*Администрация:&nbsp;*}
							<span id="editFromDate" class="fas fa-calendar-alt fa-fw" data-fa-transform="right-22 down-10" ></span>
						</div>
						<input type="text" name="sFromDate" id="sFromDate" class="form-control inp100" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$smarty.now|date_format:"%d.%m.%Y"}" />
						<div class="input-group-append">
							<i class="fas fa-arrows-alt-h fa-fw" data-fa-transform=""></i>
						</div>
						<input type="text" name="sToDate" id="sToDate" class="form-control inp1`00 input-group-addon" placeholder="__.__.____" onkeypress="return formatDate( event, '.' );" value="{$smarty.now|date_format:"%d.%m.%Y"}" />
						<div class="input-group-append">
							<i id="editToDate" class="fas fa-calendar-alt fa-fw" data-fa-transform=""></i>
						</div>
					</div>
				</td>
				<td>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fa fa-calculator-alt fa-fw"  data-fa-transform="right-22 down-10"></i>
						</div>
						<select class="form-control" name="payment_type" id="payment_type" title="Избери тип на плащане">
							<option value="all" selected="selected">Всички плащания</option>
							<option value="error">Грешни суми</option>
						</select>
					</div>
				</td>
				<td>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fa fa-tags fa-fw"  data-fa-transform="right-22 down-10"></i>
						</div>
						<select class="form-control" name="payment_provider" id="payment_provider" title="Избери провайдър">
							<option value="all" selected="selected">Всички провайдъри</option>
							{foreach from=$aProvider item=prov}
								<option value="{$prov.id}">{$prov.name}</option>
							{/foreach}
						</select>
					</div>
				</td>
				<td>
					<button class="btn btn-sm btn-success" name="reload" id="reload" type="button" onclick="loadXMLDoc2('result');"><i class="far fa-redo-alt"></i> Обнови </button>
				</td>
			</tr>
		</table>
	</div>
	<div id="line"></div>
	<div id="result"></div>
</form>
<script type="text/javascript">
	loadXMLDoc2('result');
</script>
