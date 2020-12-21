var Stats = function () {

	var _mode = 0, _scale = 1, _div = 2, _modesCount = 3, _container,
	_frames = 0, _time = new Date().getTime(), _timeLastFrame = _time, _timeLastSecond = _time,
	_fps = 0, _fpsMin = 1000, _fpsMax = 0, _fpsDiv, _fpsText, _fpsCanvas, _fpsContext, _fpsImageData,
	_bps = 0, _bpsMin = 1000, _bpsMax = 0, _bpsDiv, _bpsText, _bpsCanvas, _bpsContext, _bpsImageData,
	_ms = 0, _msMin = 1000, _msMax = 0, _msDiv, _msText, _msCanvas, _msContext, _msImageData,
	_mb = 0, _mbMin = 1000, _mbMax = 0, _mbDiv, _mbText, _mbCanvas, _mbContext, _mbImageData,
	_colors = {
		fps: {
			bg: {r: 16, g: 16, b: 48},
			fg: {r: 0, g: 255, b: 255}
		},
		ms: {
			bg: {r: 16, g: 48, b: 16},
			fg: {r: 0, g: 255, b: 0}
		},
		mb: {
			bg: {r: 48, g: 16, b: 26},
			fg: {r: 255, g: 0, b: 128}
		}
	};

	_container = document.createElement( 'div' );
	_container.style.cursor = 'pointer';
	_container.style.width = '150px';
	_container.style.opacity = '0.9';
	_container.style.zIndex = '10001';
	_container.addEventListener( 'click', swapMode, false );
	
	_bpsDiv = document.createElement( 'div' );
	//_fpsDiv.style.backgroundColor = 'rgb(' + Math.floor( _colors.fps.bg.r / 2 ) + ',' + Math.floor( _colors.fps.bg.g / 2 ) + ',' + Math.floor( _colors.fps.bg.b / 2 ) + ')';
	_bpsDiv.style.padding = '2px 0px 3px 0px';
	_container.appendChild( _bpsDiv );

	_bpsText = document.createElement( 'div' );
	_bpsText.style.fontFamily = 'Helvetica, Arial, sans-serif';
	_bpsText.style.textAlign = 'left';
	_bpsText.style.fontSize = '9px';
//	_fpsText.style.color = 'rgb(' + _colors.fps.fg.r + ',' + _colors.fps.fg.g + ',' + _colors.fps.fg.b + ')';
	_bpsText.style.color = '#023B90';
	_bpsText.style.margin = '2px 2px 1px 3px';
	_bpsText.style.float = "left";
	_bpsText.innerHTML = '<span style="font-weight:bold">BPM</span>';
	_bpsDiv.appendChild( _bpsText );
	
	_bpsCanvas = document.createElement( 'canvas' );	
	_bpsCanvas.width = 74;
	_bpsCanvas.height = 25;
	_bpsCanvas.style.display = 'block';
	_bpsCanvas.style.marginLeft = '3px';
	_bpsCanvas.style.float = 'right';
	_bpsCanvas.style.border = '1px solid #023B90';
	_bpsCanvas.style.boxShadow = ' 0 0 5px #000';
	_bpsDiv.appendChild( _bpsCanvas );
	
	_bpsContext = _bpsCanvas.getContext( '2d' );
	//_fpsContext.fillStyle = 'rgb(' + _colors.fps.bg.r + ',' + _colors.fps.bg.g + ',' + _colors.fps.bg.b + ')';
	_bpsContext.fillStyle = '#7eb8fd';
	_bpsContext.fillRect( 0, 0, _bpsCanvas.width, _bpsCanvas.height );

	_bpsImageData = _bpsContext.getImageData( 0, 0, _bpsCanvas.width, _bpsCanvas.height );

	// fps

	_fpsDiv = document.createElement( 'div' );
	//_fpsDiv.style.backgroundColor = 'rgb(' + Math.floor( _colors.fps.bg.r / 2 ) + ',' + Math.floor( _colors.fps.bg.g / 2 ) + ',' + Math.floor( _colors.fps.bg.b / 2 ) + ')';
	_fpsDiv.style.padding = '2px 0px 3px 0px';
	_fpsDiv.style.display = 'none';
	_container.appendChild( _fpsDiv );

	_fpsText = document.createElement( 'div' );
	_fpsText.style.fontFamily = 'Helvetica, Arial, sans-serif';
	_fpsText.style.textAlign = 'left';
	_fpsText.style.fontSize = '9px';
//	_fpsText.style.color = 'rgb(' + _colors.fps.fg.r + ',' + _colors.fps.fg.g + ',' + _colors.fps.fg.b + ')';
	_fpsText.style.color = '#023B90';
	_fpsText.style.margin = '2px 2px 1px 3px';
	_fpsText.style.float = "left";
	_fpsText.innerHTML = '<span style="font-weight:bold">FPS</span>';
	_fpsDiv.appendChild( _fpsText );

	_fpsCanvas = document.createElement( 'canvas' );	
	_fpsCanvas.width = 74;
	_fpsCanvas.height = 25;
	_fpsCanvas.style.display = 'block';
	_fpsCanvas.style.marginLeft = '3px';
	_fpsCanvas.style.float = 'right';
	_fpsCanvas.style.border = '1px solid #023B90';
	_fpsCanvas.style.boxShadow = ' 0 0 5px #000';
	_fpsDiv.appendChild( _fpsCanvas );

	_fpsContext = _fpsCanvas.getContext( '2d' );
	//_fpsContext.fillStyle = 'rgb(' + _colors.fps.bg.r + ',' + _colors.fps.bg.g + ',' + _colors.fps.bg.b + ')';
	_fpsContext.fillStyle = '#7eb8fd';
	_fpsContext.fillRect( 0, 0, _fpsCanvas.width, _fpsCanvas.height );

	_fpsImageData = _fpsContext.getImageData( 0, 0, _fpsCanvas.width, _fpsCanvas.height );

	// ms

	_msDiv = document.createElement( 'div' );
	//_msDiv.style.backgroundColor = 'rgb(' + Math.floor( _colors.ms.bg.r / 2 ) + ',' + Math.floor( _colors.ms.bg.g / 2 ) + ',' + Math.floor( _colors.ms.bg.b / 2 ) + ')';
	_msDiv.style.padding = '2px 0px 3px 0px';
	_msDiv.style.display = 'none';
	_container.appendChild( _msDiv );

	_msText = document.createElement( 'div' );
	_msText.style.fontFamily = 'Helvetica, Arial, sans-serif';
	_msText.style.textAlign = 'left';
	_msText.style.fontSize = '9px';
//	_msText.style.color = 'rgb(' + _colors.ms.fg.r + ',' + _colors.ms.fg.g + ',' + _colors.ms.fg.b + ')';
	_msText.style.color = '#023B90';
	_msText.style.margin = '2px 2px 1px 3px';
	_msText.style.float = "left";
	_msText.innerHTML = '<span style="font-weight:bold">MS</span>';
	_msDiv.appendChild( _msText );

	_msCanvas = document.createElement( 'canvas' );
	_msCanvas.width = 74;
	_msCanvas.height = 25;
	_msCanvas.style.display = 'block';
	_msCanvas.style.marginLeft = '3px';
	_msCanvas.style.float = 'right';
	_msCanvas.style.border = '1px solid #023B90';
	_msCanvas.style.boxShadow = ' 0 0 5px #000';
	_msDiv.appendChild( _msCanvas );

	_msContext = _msCanvas.getContext( '2d' );
	//_msContext.fillStyle = 'rgb(' + _colors.ms.bg.r + ',' + _colors.ms.bg.g + ',' + _colors.ms.bg.b + ')';
	_msContext.fillStyle = '#7eb8fd';
	_msContext.fillRect( 0, 0, _msCanvas.width, _msCanvas.height );

	_msImageData = _msContext.getImageData( 0, 0, _msCanvas.width, _msCanvas.height );

	// mb

	try { 

		if ( performance && performance.memory && performance.memory.totalJSHeapSize ) {

			_modesCount = 4;

		}

	} catch ( error ) { };

	_mbDiv = document.createElement( 'div' );
	//_mbDiv.style.backgroundColor = 'rgb(' + Math.floor( _colors.mb.bg.r / 2 ) + ',' + Math.floor( _colors.mb.bg.g / 2 ) + ',' + Math.floor( _colors.mb.bg.b / 2 ) + ')';
	_mbDiv.style.padding = '2px 0px 3px 0px';
	_mbDiv.style.display = 'none';
	_container.appendChild( _mbDiv );

	_mbText = document.createElement( 'div' );
	_mbText.style.fontFamily = 'Helvetica, Arial, sans-serif';
	_mbText.style.textAlign = 'left';
	_mbText.style.fontSize = '9px';
	//_mbText.style.color = 'rgb(' + _colors.mb.fg.r + ',' + _colors.mb.fg.g + ',' + _colors.mb.fg.b + ')';
	_mbText.style.color = '#023B90';
	_mbText.style.margin = '2px 2px 1px 3px';
	_mbText.style.float = "left";
	_mbText.innerHTML = '<span style="font-weight:bold">MB</span>';
	_mbDiv.appendChild( _mbText );

	_mbCanvas = document.createElement( 'canvas' );
	_mbCanvas.width = 74;
	_mbCanvas.height = 25;
	_mbCanvas.style.display = 'block';
	_mbCanvas.style.marginLeft = '3px';
	_mbCanvas.style.float = 'right';
	_mbCanvas.style.border = '1px solid #023B90';
	_mbCanvas.style.boxShadow = ' 0 0 5px #000';
	_mbDiv.appendChild( _mbCanvas );

	_mbContext = _mbCanvas.getContext( '2d' );
	_mbContext.fillStyle = '#7eb8fd';
	_mbContext.fillRect( 0, 0, _mbCanvas.width, _mbCanvas.height );

	_mbImageData = _mbContext.getImageData( 0, 0, _mbCanvas.width, _mbCanvas.height );

	function updateGraph( data, value, color, div ) {
		
		var x, y, index;	

		for ( y = 0; y < 25; y++ ) {

			for ( x = 0; x < 73; x++ ) {

				index = (x + y * 74) * 4;
				
				data[ index ] = data[ index + 4 ];
				data[ index + 1 ] = data[ index + 5 ];
				data[ index + 2 ] = data[ index + 6 ];				
			}

		}		
		for ( y = 0; y < 25; y++ ) {

			index = (73 + y * 74) * 4;

			if ( y < value ) {

				data[ index ] = 126;
				data[ index + 1 ] = 184;
				data[ index + 2 ] = 253;

			} else {

				data[ index ] = 0;
				data[ index + 1 ] = 60;
				data[ index + 2 ] = 145;

			}			
		}

	}

	function swapMode(e) {
		if (e.button==1) {
			_scale = _scale < 4 ? _scale + 1 : 1;
			_div = _scale*2;
			console.log("Utilization scale mode: " + _scale);			
			return;
		}

		_mode ++;
		_mode == _modesCount ? _mode = 0 : _mode;

		_bpsDiv.style.display = 'none';
		_fpsDiv.style.display = 'none';
		_msDiv.style.display = 'none';
		_mbDiv.style.display = 'none';

		switch( _mode ) {
			case 0:
				_bpsDiv.style.display = 'block';
			break;
			case 1:

				_fpsDiv.style.display = 'block';

				break;

			case 2:

				_msDiv.style.display = 'block';

				break;

			case 3:

				_mbDiv.style.display = 'block';

				break;
		}

	}

	return {

		domElement: _container,

		update: function () {

			_frames ++;

			_time = new Date().getTime();

			_ms = _time - _timeLastFrame;
			_msMin = Math.min( _msMin, _ms );
			_msMax = Math.max( _msMax, _ms );

			updateGraph( _msImageData.data, Math.min( 25, 25 - ( _ms / 200 ) * 25 ), 'ms' );

			_msText.innerHTML = '<span style="font-weight:bold">' + _ms + ' MS</span></br><span>(' + _msMin + '-' + _msMax + ')</span>';
			_msContext.putImageData( _msImageData, 0, 0 );

			_timeLastFrame = _time;
			
			if ( _time > _timeLastSecond + 1000 ) {								
				_bps = Math.round(_time / 1000) > sockBytesReceived[59].ts ? 0 : Math.round(sockBytesReceived[59].len/1024*1000)/1000;				
				_bpsMin =  Math.round(Math.min(_bpsMin, _bps));				
				_bpsMax =  Math.round(Math.max(_bpsMax, _bps));
				(_bpsMin == 0) && (_bpsMin = _bpsMax);
				updateGraph( _bpsImageData.data, Math.min( 25, 25 - Math.round(_bps)*_scale), 'bps' );
				_bpsText.innerHTML = '<span style="font-weight:bold">' + _bps + ' kB/s</span></br><span>(' + _bpsMin  + '-' + _bpsMax + ')</span>';
				_bpsContext.putImageData( _bpsImageData, 0, 0 );

				_fps = Math.round( ( _frames * 1000) / ( _time - _timeLastSecond ) );
				_fpsMin = Math.min( _fpsMin, _fps );
				_fpsMax = Math.max( _fpsMax, _fps );

				updateGraph( _fpsImageData.data, Math.min( 25, 25 - ( _fps / 100 ) * 25 ), 'fps' );

				_fpsText.innerHTML = '<span style="font-weight:bold">' + _fps + ' FPS</span></br><span>(' + _fpsMin + '-' + _fpsMax + ')</span>';
				_fpsContext.putImageData( _fpsImageData, 0, 0 );

				if ( _modesCount == 4 ) {

					_mb = performance.memory.usedJSHeapSize * 0.000000954;
					_mbMin = Math.min( _mbMin, _mb );
					_mbMax = Math.max( _mbMax, _mb );					
										
					updateGraph( _mbImageData.data, Math.min( 25, 25 - (_mb / _div)), 'mb' );

					_mbText.innerHTML = '<span style="font-weight:bold">' + Math.round( _mb ) + ' MB</span></br><span>(' + Math.round( _mbMin ) + '-' + Math.round( _mbMax ) + ')</span>';
					_mbContext.putImageData( _mbImageData, 0, 0 );

				}

				_timeLastSecond = _time;
				_frames = 0;

			}

		}

	};

};
