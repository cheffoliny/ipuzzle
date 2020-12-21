  	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			id="{$page}" width="100%" height="100%"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
			<param name="movie" value="{$play_flex_file}" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#f2f2f2" />
			<param name="allowScriptAccess" value="sameDomain" />
			<param name='flashVars' value='{$flex_params}'/>
			<embed src="{$play_flex_file}" quality="high" bgcolor="#f2f2f2"
				name="{$page}" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer" width="100%" height="100%">
			</embed>
	</object>
