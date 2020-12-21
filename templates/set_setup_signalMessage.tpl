{literal}
<script>
	rpc_debug = true;

	function testSignal(obj) {
		var test 	= obj.options[obj.selectedIndex].id.split(',');
		var notTest = document.getElementById('nIDTest');
        var isZone  = document.getElementById('is_zone');
        var isSector= document.getElementById('is_sector');
		var nID 	= document.getElementById('nID').value;
		var sAlarm 	= document.getElementById('sAlarmName');
		var sRest 	= document.getElementById('sRestoreName');
		
		document.getElementById('is_test').value = test[0];

		if ( test[0] == 1 ) {
			notTest.disabled = false;
            isZone.style.display = 'none';
            isSector.style.display = 'none';
        } else {
			notTest.value = 0;
			notTest.disabled = true;
            isZone.style.display = 'block';
            isSector.style.display = 'block';
		}

		if ( nID == 0 ) {
			sAlarm.value = test[1];
			sRest.value = test[2];
		}
	}
	
	function testRadio(obj) {
		var span1 = document.getElementById('selAlarm1');
		var span2 = document.getElementById('selAlarm2');
		var span3 = document.getElementById('selRestore1');
		var span4 = document.getElementById('selRestore2');
		
		var cid1 = document.getElementById('sIDAlarmRadio');
		var cid2 = document.getElementById('sIDRestoreRadio');

		var rCode1 = $('sIDSignalAlarm').value;
		var rCode2 = $('sIDSignalRest').value;
		
		if ( (obj.value == 'phone') || (obj.value == 'cid') ) {
			span1.style.display = 'none';
			span2.style.display = 'block';
			span3.style.display = 'none';
			span4.style.display = 'block';
			
			//if (obj.value == 'cid') {
				cid1.disabled = false;
				cid2.disabled = false;
			//} else {
			//	cid1.disabled = false;
			//	cid2.disabled = false;
				
			//	cid1.value = rCode1;
			//	cid2.value = rCode2;
			//}
		} else {
			span1.style.display = 'block';
			span2.style.display = 'none';
			span3.style.display = 'block';
			span4.style.display = 'none';
			cid1.disabled = false;
			cid2.disabled = false;			
		}

	}
	

</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" onsubmit="loadXMLDoc2( 'save', 3 ); return false">
    <div class="modal-content pb-3">
        <div class="modal-header">
            <h6 class="modal-title text-white" id="exampleModalLabel">{if $nID}Редакция на{else}Добавяне на{/if} сигнал</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">

            <input type="hidden" id="nID" name="nID" value="{$nID}"					/>
            <input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}"	/>
            <input type="hidden" id="flag" name="flag" value="0"					/>
            <input type="hidden" id="is_test" name="is_test" value="0"				/>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fa fa-signal fa-fw" data-fa-transform="right-22 down-10" title="Избери сигнал..."></span>
                        </div>
                        <select class="form-control" name="nIDSignal" id="nIDSignal" onChange="testSignal(this);" title="Избери сигнал..."></select>
                    </div>
                </div>
            </div>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="fa fa-random fa-fw" data-fa-transform="right-22 down-10" title="Избери канал за комуникация..."></span>
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
                            <i class="far fa-bell fa-fw" data-fa-transform="right-22 down-10" title="Алармиращ код..."></i>
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
                            <i class="far fa-bell fa-fw" data-fa-transform="right-22 down-10" title="Алармиращ код..."></i>
                        </div>
                        <input class="form-control" type="text" id="sIDAlarmRadio" name="sIDAlarmRadio" />
                    </div>
                </div>
            </div>

            <div class="row mb-1">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="far fa-bell fa-fw" data-fa-transform="right-22 down-10" title="Алармено съобщение..."></span>
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
                            <i class="far fa-bell-slash fa-fw" data-fa-transform="right-22 down-10" title="Възстановяващ код..."></i>
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
                            <i class="far fa-bell-slash fa-fw" data-fa-transform="right-22 down-10" title="Възстановяващ код..."></i>
                        </div>

                        <input class="form-control" type="text" id="sIDRestoreRadio" name="sIDRestoreRadio" />
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 pl-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="far fa-bell-slash fa-fw" data-fa-transform="right-22 down-10" title="Възстановяващо съобщение..."></span>
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
                            <span class="fa fa-cube fa-fw" data-fa-transform="right-22 down-10" title="Охраняван сектор..."></span>
                        </div>
                        <input class="form-control w-75 mr-1" name="sName" id="sName" disabled="disabled" placeholder=" Охраняван сектор..."/>
                        <input class="input-group-addon form-control text-primary" type="checkbox" id="is_sector" name="is_sector" />
                    </div>
                </div>
            </div>
        <div class="row mb-1">
            <div class="col-sm-12 pl-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fa fa-cubes fa-fw" data-fa-transform="right-22 down-10" title="Охранявана зона..."></span>
                    </div>
                    <input class="form-control w-75 mr-1" name="zName" id="zName" disabled="disabled" placeholder="Охранявана зона..."/>
                    <input class="input-group-addon form-control" type="checkbox" id="is_zone" name="is_zone" />
                </div>
            </div>
        </div>
        <div class="row mb-5 pb-5">
            <div class="col-sm-12 pl-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fa fa-text-width fa-fw" data-fa-transform="right-22 down-10" title="Избери период на повторение..."></span>
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
                    <input class="input-group-addon form-control" type="checkbox" id="active" name="active" />
                </div>
            </div>
        </div>

    </div>

    <nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-0" id="search">
        <div class="col-12">
            <div class="input-group input-group-sm ml-1">
                <button class="btn btn-sm btn-block btn-primary" type="submit"><i class="fas fa-check"></i> Запази</button>
            </div>
        </div>
    </nav>

</form>

{literal}
<script>

    loadXMLDoc2('load');

</script>
{/literal}