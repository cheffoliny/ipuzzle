{literal}
    <script>
        rpc_debug = true;


        var onLoad = function () {
            loadXMLDoc2('init');

            rpc_on_exit = function () {
                if (jQuery('#nID').val() != 0) {
                    disableAll();
                }

                if (jQuery('#is_confirmed').val() > 0) {
//                    jQuery('#printLeave').show();
                    if(jQuery('#pdf_person_leave_without_num_and_date').val()) {
                        if(jQuery('#pdf_person_leave_without_num_and_date_userdata').val()) {
                            jQuery('#printLeave').show();
                        }
                    }else {
                        jQuery('#printLeave').show();
                    }
                }

                if (jQuery('#nIsAllowed').prop('checked')) {
                    enableResolution();
                } else {
                    disableResolution();
                }

                rpc_on_exit = function () {
                };
            }
        };

        jQuery(document).ready(function () {
            var nID = jQuery('#nID').val();
            var nIDPerson = jQuery('#id_person').val();

            onLoad();


            jQuery('#saveLeave').click(function () {
                loadXMLDoc2('save');
                rpc_on_exit = function () {
                    location.reload();
                    rpc_on_exit = function () {
                    };
                }
                return false;
            });

            jQuery('#saveResLeave').click(function () {
                loadXMLDoc2('confirm');
                rpc_on_exit = function () {
                    location.reload();
                    rpc_on_exit = function () {
                    };
                }
                return false;
            });

            jQuery('#printLeave').click(function () {
                loadDirect('printPDF');
                return false;
            });

            jQuery('#nIsAllowed').click(function () {
                console.log('test');
                if (jQuery('#nIsAllowed').prop('checked')) {
                    enableResolution();
                } else {
                    disableResolution();
                }
            });

            if (nID != 0 && nIDPerson != 0) {
                showResolution();
            }

            jQuery('#nIDCodeLeave').on('change',function(){
                loadXMLDoc2('changeLeaveType');
                jQuery('#sLeaveType').css('pointer-events','none');
            });

        });

        var disableAll = function () {
            jQuery('#form1 #application input').attr('readonly', 'readonly');
            jQuery('#form1 #application select').attr('disabled', 'disabled');
            jQuery('#saveLeave').remove();
        }

        var showResolution = function () {
            jQuery('#resolution').show();
            disableResolution();
        }

        var enableResolution = function () {
            jQuery('#form1 #resolution input').removeAttr('readonly');
            jQuery('#form1 #resolution select').removeAttr('disabled');
            jQuery('.resolution-checkbox-label').html('Разрешава ');
            jQuery('#saveResLeave').show();
            jQuery('.resolution-section').show();

        }

        var disableResolution = function () {
            jQuery('#form1 #resolution input').attr('readonly', 'readonly');
            jQuery('#form1 #resolution select').attr('disabled', 'disabled');
//            jQuery('#saveResLeave').hide();
            jQuery('.resolution-section').hide();
            jQuery('.resolution-checkbox-label').html('Не разрешава ');
        }


    </script>
    <style>
        input:not([type='checkbox']), select {
            height: 28px !important;
        }
    </style>
{/literal}
{if $nID eq 0}
    <dlcalendar click_element_id="leaveFromBtn" input_element_id="sLeaveFromOffer" tool_tip="Изберете дата"
                id="dlCalendarForLeave"></dlcalendar>
{/if}
<dlcalendar click_element_id="btnResCalendar" input_element_id="sLeaveFrom" tool_tip="Изберете дата"
            id="dlCalendarForLeaveRes"></dlcalendar>
<form method="POST" name="form1" id="form1" onsubmit="return false;">
    <div class="modal-content pb-3">
        <div class="modal-header">
            {if $nID}Редакция на{else}Молба за новa{/if} отпуска {if $nID}№ <input type="text" id="nLeaveNum" class="transparent"/>{/if}
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            <input type="hidden" id="nID" name="nID" value="{$nID}">
            <input type="hidden" id="id_person" name="id_person" value="{$nIDPerson}">
            <input type="hidden" id="is_confirmed" name="is_confirmed" value="">

            <input type="hidden" id="pdf_person_leave_without_num_and_date_userdata" name="pdf_person_leave_without_num_and_date_userdata" value="{$pdf_person_leave_without_num_and_date_userdata}">
            <input type="hidden" id="pdf_person_leave_without_num_and_date" name="pdf_person_leave_without_num_and_date" value="{$pdf_person_leave_without_num_and_date}">

            <fieldset id="application">
                {*<legend>Молба</legend>*}
                <div class="container form-horizontal">
                    <div class="form-group">
                        <div class="row">
                            {*<label for="nIDPersonSubstitute" class="control-label col-sm-2">Служител:</label>*}
                            <div class="col-sm-10">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <i class="fa fa-user-circle fa-fw"  data-fa-transform="right-22 down-10" title="Служител"></i>
                                    </div>
                                    <input type="text" title="Служител" placeholder="Служител" name="sPersonName" id="sPersonName" class="form-control"  readonly="readonly" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-group-sm">
                        <div class="row">
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <i class="fa fa-calendar-day fa-fw"  data-fa-transform="right-22 down-10" title="Брой дни"></i>
                                    </div>
                                    <input class="form-control" type="text" title="Дни" placeholder="Дни" name="nApplicationDaysOffer" id="nApplicationDaysOffer" value="1" />
                                </div>
                                {*<label>Дни</label>*}
                                {*<input type="text" class="form-control input-sm" id="nApplicationDaysOffer"*}
                                {*name="nApplicationDaysOffer" value="1">*}
                            </div>
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <i class="fa fa-tag fa-fw"  data-fa-transform="right-22 down-10" title="Брой дни"></i>
                                    </div>
                                    <select class="form-control"
                                            title="Тип"
                                            readonly="readonly"
                                            id="sLeaveType"
                                            name="sLeaveType">
                                        <option value="due"> Платен</option>
                                        <option value="unpaid"> Неплатен</option>
                                    </select>
                                </div>
                                {*<label for="nIDCodeLeave">Тип</label>*}
                                {*<select name="sLeaveType" id="sLeaveType" readonly="readonly" class="form-control input-sm">*}
                                {*<option value="due"> Платен</option>*}
                                {*<option value="unpaid"> Неплатен</option>*}
                                {*</select>*}
                            </div>
                            <div class="col-6"></div>
                        </div>
                    </div>
                    <div class="form-group form-group-sm">
                        <div class="row">
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <i class="fa fa-tag fa-fw"  data-fa-transform="right-22 down-10" title="Брой дни"></i>
                                    </div>
                                    <input type="text" title="От" placeholder="От" name="sLeaveFromOffer" id="sLeaveFromOffer" readonly class="form-control" />
                                    <span class="input-group-append"  id="leaveFromBtn"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                {*<label>От</label>*}
                                {*<div class="input-group">*}
                                {*<input type="text" class="form-control input-sm" id="sLeaveFromOffer"*}
                                {*name="sLeaveFromOffer"*}
                                {*readonly>*}
                                {*<span class="input-group-append"  id="leaveFromBtn"><i class="far fa-calendar-alt"></i></span>*}
                            </div>
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <i class="fa fa-calendar-check-o fa-fw"  data-fa-transform="right-22 down-10" title="Брой дни"></i>
                                    </div>
                                    <select class="form-control" title="Година" id="forYear" name="forYear"></select>
                                    <span class="input-group-append"><i class="far fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-6"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            {*<label for="nIDPersonSubstitute" class="control-label col-sm-2">Заместник:</label>*}
                            <div class="col-sm-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon wd">Заместник</span>
                                    <select class="form-control form-control-select200"
                                            title="Заместник"
                                            id="nIDPersonSubstitute"
                                            name="nIDPersonSubstitute">
                                    </select>
                                </div>
                                {*<select name="nIDPersonSubstitute" id="nIDPersonSubstitute" class="form-control input-sm">*}
                                {*</select>*}
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-group-sm">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon wd ">Чл. от КТ</span>
                                    <select class="form-control form-control-select200"
                                            title="Чл. от КТ"
                                            id="nIDCodeLeave"
                                            name="nIDCodeLeave">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button id="saveLeave" type="submit" class="btn btn-sm btn-success float-right">Запази</button>
                        </div>
                    </div>
            </fieldset>

            <fieldset style="display: none;" id="resolution">
                <legend>Резолюция</legend>
                <div class="container">
                    <div class="form-inline">
                        <div class="row">
                            <div class="input-group input-group-sm">
                                <span class="input-group-addon">
                                    <input type="checkbox" id="nIsAllowed" name="nIsAllowed">
                                </span>
                                <span class="input-group-addon resolution-checkbox-label">Разрешава</span>
                            </div>
                            {*<div class="checkbox ">*}
                            {*<label>*}
                            {*<span class="resolution-checkbox-label">Разрешава </span><input type="checkbox" name="nIsAllowed" id="nIsAllowed">*}
                            {*</label>*}
                            {*</div>*}
                            <div class="form-group resolution-section">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon wd">Работни дни</span>
                                    <input type="text"
                                           title="Работни дни"
                                            {*placeholder="Работни дни"*}
                                           name="nApplicationDays"
                                           id="nApplicationDays"
                                           class="form-control form-control-inp50">
                                </div>
                                {*<label for="exampleInputPassword3">Работни дни</label>*}
                                {*<input type="text" class="form-control-inp50" id="nApplicationDays" name="nApplicationDays">*}
                            </div>
                            {*<div class="form-group resolution-section">*}
                            {*<label>&nbsp;,считани от</label>*}
                            {*</div>*}
                            <div class="form-group resolution-section">
                                <div class="input-group input-group-sm" style="width: 220px;">
                                    <span class="input-group-addon">
                                        &nbsp;,считани от
                                    </span>
                                    <input type="text" class="form-control-inp50" id="sLeaveFrom" name="sLeaveFrom">
                                    <span class="input-group-append">
                                        <i class="far fa-calendar-alt"
                                           id="btnResCalendar">
                                        </i>
                                    </span>
                                </div>
                            </div>
                            {if $bRightResolute}
                                <button type="button" class="btn btn-sm btn-success float-right" style="display: none;"
                                        id="saveResLeave">Потвърди
                                </button>
                            {/if}
                            <button type="button" class="btn btn-sm btn-default float-right" style="display: none;" id="printLeave">
                                Печат
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</form>