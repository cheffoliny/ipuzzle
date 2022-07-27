{literal}
    <script>

        rpc_debug = true;

        function save()
        {
            loadXMLDoc2('save',3);
        }

        InitSuggestForm = function() {
            for( var i = 0; i < suggest_elements.length; i++ ) {

                if ( suggest_elements[i]['id'] == 'sClient' ) {
                    suggest_elements[i]['suggest'].setSelectionListener( onSuggestClient );
                }

            }
        }

        function loadTemplate() {
            loadXMLDoc2('loadTemplate');
        }


        function onSuggestClient( aParams )
        {
            var sKey = aParams.KEY;
            var aKey = sKey.split( ";" );

            jQuery('#nIDClient').val(aKey[0]);

            loadTemplate();
        }

        function openClient() {
            var idClient = jQuery('#nIDClient').val();

            if(empty(idClient)) {
                alert('Няма избран клиент!');
            } else {
                dialogClientInfo(idClient);
            }
        }


    </script>

    <style>
        .wd {
            width: 170px;
        }
    </style>
{/literal}

<div class="content">
    <dlcalendar click_element_id="sSendDateImg" input_element_id="sSendDate" tool_tip="Дата на изпращане"></dlcalendar>
    <dlcalendar click_element_id="sReceiveDateImg" input_element_id="sReceiveDate" tool_tip="Дата на получване"></dlcalendar>
    <form id="form1" name="form1" onsubmit="return false;">
        <div class="page_caption">Добавяне на нотификация</div>

        <table class="table-condensed" style="margin: 0 auto;">
            <tr class="odd">
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group input-group-addon wd">Нотификация:</span>
                        <select name="nIDNotification" id="nIDNotification" class="form-control form-control-select250">
                        </select>
                    </div>
                </td>
            </tr>


            <tr class="even">
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group input-group-addon wd">Клиент:</span>
                        <input type="text" name="sClient" id="sClient" suggest="suggest" querytype="ClientName" class="form-control form-control-inp250" style="    width: 225px;" />
                        <input type="hidden" name="nIDClient" id="nIDClient" />
                        <span id="openClient" class="input-group-addon" onclick="openClient();"><i class="far fa-folder-open"></i></span>
                    </div>
                </td>
            </tr>

            <tr class="even">
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group input-group-addon wd">Телефон за известяване:</span>
                        <input type="text" name="sPhone" id="sPhone"  class="form-control form-control-inp250" readonly="readonly" />
                    </div>
                </td>
            </tr>

            <tr class="even">
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group input-group-addon wd">Неплатени такси:</span>
                        <input type="text" name="nCount" id="nCount" class="form-control form-control-inp250" onchange="loadTemplate()" />
                    </div>
                </td>
            </tr>

            <tr class="odd">
                <td>
                    <textarea id="sTemplate" name="sTemplate" style="width: 425px; height: 100px;" placeholder="Шаблон" readonly="readonly"></textarea>
                </td>
            </tr>

            <tr class="odd">
                <td style="text-align:right;">
                    <button type="button" onClick="save();return false;" class="btn btn-sm btn-success"><span class="far fa-save"></span> Запиши </button>
                    <button onClick="parent.window.close();" class="btn btn-sm btn-danger"><i class="far fa-times"></i> Затвори </button>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
    loadXMLDoc2('load');
</script>