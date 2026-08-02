{literal}
<script>
	rpc_debug = true;

	function testSignal(obj) {
		if (!obj || obj.selectedIndex < 0 || !obj.options[obj.selectedIndex]) {
			return;
		}

		var optionMeta = obj.options[obj.selectedIndex].id || '';
		var test 	= optionMeta.split(',');
		var notTest = document.getElementById('nIDTest');
        var isZone  = document.getElementById('is_zone');
        var isSector= document.getElementById('is_sector');
		var nID 	= document.getElementById('nID').value;
		var sAlarm 	= document.getElementById('sAlarmName');
		var sRest 	= document.getElementById('sRestoreName');
		
		document.getElementById('is_test').value = test[0] || 0;

		if ( test[0] == '1' ) {
			notTest.disabled = false;
            isZone.closest('.ui-message-toggle').style.display = 'none';
            isSector.closest('.ui-message-toggle').style.display = 'none';
        } else {
			notTest.value = 0;
			notTest.disabled = true;
            isZone.closest('.ui-message-toggle').style.display = 'inline-flex';
            isSector.closest('.ui-message-toggle').style.display = 'inline-flex';
		}

		if ( nID == 0 ) {
			sAlarm.value = test[1] || '';
			sRest.value = test[2] || '';
		}
	}
	
	function testRadio(obj) {
		var span1 = document.getElementById('selAlarm1');
		var span2 = document.getElementById('selAlarm2');
		var span3 = document.getElementById('selRestore1');
		var span4 = document.getElementById('selRestore2');
		
		var cid1 = document.getElementById('sIDAlarmRadio');
		var cid2 = document.getElementById('sIDRestoreRadio');

		if (!obj) {
			return;
		}

		if ( (obj.value == 'phone') || (obj.value == 'cid') ) {
			span1.style.display = 'none';
			span2.style.display = 'block';
			span3.style.display = 'none';
			span4.style.display = 'block';
			
			cid1.disabled = false;
			cid2.disabled = false;
		} else {
			span1.style.display = 'block';
			span2.style.display = 'none';
			span3.style.display = 'block';
			span4.style.display = 'none';
			cid1.disabled = false;
			cid2.disabled = false;			
		}

	}

	function syncSignalMessageForm() {
		testRadio(document.getElementById('sIDChannel'));
		testSignal(document.getElementById('nIDSignal'));
	}

</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-message-dialog" onsubmit="loadXMLDoc2( 'save', 3 ); return false;">
    <div class="modal-content ui-message-dialog-content">
        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция на{else}Добавяне на{/if} сигнал</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body ui-message-dialog-body">

            <input type="hidden" id="nID" name="nID" value="{$nID}"					/>
            <input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}"	/>
            <input type="hidden" id="flag" name="flag" value="0"					/>
            <input type="hidden" id="is_test" name="is_test" value="0"				/>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-signal" aria-hidden="true" title="Избери сигнал..."></span>
                        </div>
                        <select class="form-control" name="nIDSignal" id="nIDSignal" onChange="testSignal(this);" title="Избери сигнал..."></select>
                    </div>
                </div>
            </div>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-exchange" aria-hidden="true" title="Избери канал за комуникация..."></span>
                        </div>
                        <select class="form-control" name="sIDChannel" id="sIDChannel" onChange="testRadio(this);" >
                            <option value="cid"	 >Комуникация - CID</option>
                            <option value="radio">Комуникация - Радио</option>
                            <option value="phone">Комуникация - Телефон</option>
                        </select>
                    </div>
                </div>
            </div>
            {*Алармиращите събития*}
            <div class="row mb-1">
                <div class="col-12 pl-1 mt-3" id="selAlarm1" name="selAlarm1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend top-20 left-10 z-1" title="Алармиращ код...">
                            <span class="ui-icon ui-icon-bell" aria-hidden="true" title="Алармиращ код..."></span>
                        </div>
                        <select class="form-control" name="sIDSignalAlarm" id="sIDSignalAlarm" >
                            <option value="0">Изберете</option>
                            <option value="1">[1] Alarm Zone 1</option>
                            <option value="11">[11] Restore Zone 1</option>
                            <option value="2">[2] Alarm Zone 2</option>
                            <option value="12">[12] Restore Zone 2</option>
                            <option value="3">[3] Alarm Zone 3</option>
                            <option value="13">[13] Restore Zone 3</option>
                            <option value="4">[4] Alarm Zone 4</option>
                            <option value="14">[14] Restore Zone 4</option>
                            <option value="5">[5] Alarm Zone 5</option>
                            <option value="15">[15] Restore Zone 5</option>
                            <option value="6">[6] Alarm Zone 6</option>
                            <option value="16">[16] Restore Zone 6</option>
                            <option value="7">[7] Alarm Zone 7</option>
                            <option value="17">[17] Restore Zone 7</option>
                            <option value="8">[8] Alarm Zone 8</option>
                            <option value="18">[18] Restore Zone 8</option>
                            <option value="39">[39] Testing</option>
                            <option value="3a">[3a] Opening</option>
                            <option value="42">[42] Closing</option>
                            <option value="33">[33] AC Loss</option>
                            <option value="3b">[3b] AC Normal</option>
                            <option value="34">[34] Low Batt</option>
                            <option value="3c">[3c] Batt Normal</option>
                            <option value="37">[37] Starting</option>
                            <option value="21">[21] Tamp Zone 1</option>
                            <option value="22">[22] Tamp Zone 2</option>
                            <option value="23">[23] Tamp Zone 3</option>
                            <option value="24">[24] Tamp Zone 4</option>
                            <option value="25">[25] Tamp Zone 5</option>
                            <option value="26">[26] Tamp Zone 6</option>
                            <option value="27">[27] Tamp Zone 7</option>
                            <option value="28">[28] Tamp Zone 8                 </option>
                            <option value="d0">[d0] Fire (wega 6)               </option>
                            <option value="d1">[d1] Restore Fire (wega 6)       </option>
                            <option value="d2">[d2] Panic (wega 6)              </option>
                            <option value="d3">[d3] Restore Panic (wega 6)      </option>
                            <option value="e4">[e4] Fuse Trouble (wega 6)       </option>
                            <option value="e5">[e5] Restore Fuse (wega 6)       </option>
                            <option value="d6">[d6] Bypass Zone (wega 6)        </option>
                            <option value="d7">[d7] Restore Bypass (wega 6)     </option>
                            <option value="e6">[e6] Engineer Entry (wega 6)     </option>
                            <option value="e7">[e7] Exit Engineer (wega 6)      </option>
                            <option value="e8">[e8] Entry Time (wega 6)         </option>
                            <option value="e9">[e9] Restore Entry Time (wega 6) </option>
                        </select>
                    </div>
                </div>
                <div class="col-12 pl-1 mt-3" id="selAlarm2" name="selAlarm2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend top-20 left-10 z-1" title="Алармиращ код...">
                            <span class="ui-icon ui-icon-bell" aria-hidden="true" title="Алармиращ код..."></span>
                        </div>
                        <input class="form-control" type="text" id="sIDAlarmRadio" name="sIDAlarmRadio" />
                    </div>
                </div>
            </div>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-bell" aria-hidden="true" title="Алармено съобщение..."></span>
                        </div>
                        <input class="form-control" type="text" id="sAlarmName" name="sAlarmName" placeholder="Алармено съобщение" />
                    </div>
                </div>
            </div>
            {*Край на алармиращите събития*}
            {*Възстановяващи събития*}
            <div class="row mb-1">
                <div class="col-12 pl-1 mt-3" id="selRestore1" name="selRestore1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend top-20 left-10 z-1" title="Възстановяващ код...">
                            <span class="ui-icon ui-icon-bell-off" aria-hidden="true" title="Възстановяващ код..."></span>
                        </div>
                        <select class="form-control" name="sIDSignalRest" id="sIDSignalRest">
                            <option value="0">Изберете</option>
                            <option value="1">[1] Alarm Zone 1</option>
                            <option value="11">[11] Restore Zone 1</option>
                            <option value="2">[2] Alarm Zone 2</option>
                            <option value="12">[12] Restore Zone 2</option>
                            <option value="3">[3] Alarm Zone 3</option>
                            <option value="13">[13] Restore Zone 3</option>
                            <option value="4">[4] Alarm Zone 4</option>
                            <option value="14">[14] Restore Zone 4</option>
                            <option value="5">[5] Alarm Zone 5</option>
                            <option value="15">[15] Restore Zone 5</option>
                            <option value="6">[6] Alarm Zone 6</option>
                            <option value="16">[16] Restore Zone 6</option>
                            <option value="7">[7] Alarm Zone 7</option>
                            <option value="17">[17] Restore Zone 7</option>
                            <option value="8">[8] Alarm Zone 8</option>
                            <option value="18">[18] Restore Zone 8</option>
                            <option value="39">[39] Testing</option>
                            <option value="3a">[3a] Opening</option>
                            <option value="42">[42] Closing</option>
                            <option value="33">[33] AC Loss</option>
                            <option value="3b">[3b] AC Normal</option>
                            <option value="34">[34] Low Batt</option>
                            <option value="3c">[3c] Batt Normal</option>
                            <option value="37">[37] Starting</option>

                            <option value="21">[21] Tamp Zone 1</option>
                            <option value="22">[22] Tamp Zone 2</option>
                            <option value="23">[23] Tamp Zone 3</option>
                            <option value="24">[24] Tamp Zone 4</option>
                            <option value="25">[25] Tamp Zone 5</option>
                            <option value="26">[26] Tamp Zone 6</option>
                            <option value="27">[27] Tamp Zone 7</option>
                            <option value="28">[28] Tamp Zone 8</option>

                            <option value="d0">[d0] Fire (wega 6)</option>
                            <option value="d1">[d1] Restore Fire (wega 6)</option>
                            <option value="d2">[d2] Panic (wega 6)</option>
                            <option value="d3">[d3] Restore Panic (wega 6)</option>
                            <option value="e4">[e4] Fuse Trouble (wega 6)</option>
                            <option value="e5">[e5] Restore Fuse (wega 6)</option>
                            <option value="d6">[d6] Bypass Zone (wega 6)</option>
                            <option value="d7">[d7] Restore Bypass (wega 6)</option>
                            <option value="e6">[e6] Engineer Entry (wega 6)</option>
                            <option value="e7">[e7] Exit Engineer (wega 6)</option>
                            <option value="e8">[e8] Entry Time (wega 6)</option>
                            <option value="e9">[e9] Restore Entry Time (wega 6)</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 pl-1 mt-3" id="selRestore2" name="selRestore2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend top-20 left-10 z-1" title="Възстановяващ код...">
                            <span class="ui-icon ui-icon-bell-off" aria-hidden="true" title="Възстановяващ код..."></span>
                        </div>

                        <input class="form-control" type="text" id="sIDRestoreRadio" name="sIDRestoreRadio" />
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-bell-off" aria-hidden="true" title="Възстановяващо съобщение..."></span>
                        </div>
                        <input class="form-control" type="text" id="sRestoreName" name="sRestoreName" placeholder="Възстановяващо съобщение" title="Възстановяващо съобщение" />
                    </div>
                </div>
            </div>
            {*Възстановяващи събития*}

            <div class="row mb-1">
                <div class="col-sm-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-cube" aria-hidden="true" title="Охраняван сектор..."></span>
                        </div>
                        <input class="form-control w-75 mr-1" name="sName" id="sName" disabled="disabled" placeholder=" Охраняван сектор..."/>
                        <label class="ui-message-toggle" for="is_sector"><input type="checkbox" id="is_sector" name="is_sector" /><span>Сектор</span></label>
                    </div>
                </div>
            </div>
        <div class="row mb-1">
            <div class="col-sm-12 pl-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-cubes" aria-hidden="true" title="Охранявана зона..."></span>
                    </div>
                    <input class="form-control w-75 mr-1" name="zName" id="zName" disabled="disabled" placeholder="Охранявана зона..."/>
                    <label class="ui-message-toggle" for="is_zone"><input type="checkbox" id="is_zone" name="is_zone" /><span>Зона</span></label>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-sm-12 pl-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-clock" aria-hidden="true" title="Избери период на повторение..."></span>
                    </div>
                    <select class="form-control w-75 mr-1" name="nIDTest" id="nIDTest" disabled="disabled" >
                        <option value="0">Не е тестов</option>
                        <option value="2">2 мин</option>
                        <option value="5">5 мин</option>
                        <option value="30">30 мин</option>
                        <option value="60">1 час</option>
                        <option value="120">2 часа</option>
                        <option value="180">3 часа</option>
                        <option value="360">6 часа</option>
                        <option value="720">12 часа</option>
                        <option value="1440">24 часа</option>
                        <option value="2880">48 часа</option>
                    </select>
                    <label class="ui-message-toggle" for="active"><input type="checkbox" id="active" name="active" /><span>Активен</span></label>
                </div>
            </div>
        </div>

    </div>

    <nav class="navbar fixed-bottom ui-message-dialog-actions" id="search" aria-label="Действия със съобщението">
        <button class="btn btn-sm btn-primary" type="submit"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запази</button>
        <button class="btn btn-sm btn-danger" type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
    </nav>

</form>

{literal}
<script>

    rpc_on_exit = syncSignalMessageForm;
    loadXMLDoc2('load');

</script>
{/literal}
