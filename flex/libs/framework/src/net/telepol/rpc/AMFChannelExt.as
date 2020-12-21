package net.telepol.rpc {

	import mx.messaging.channels.AMFChannel;
	import mx.utils.URLUtil;
	
	import net.telepol.log.RemoteLogDispacher;
	import net.telepol.rpc.event.RemoteLogEvent;
	import net.telepol.utils.UrlUtils;
	
	
	public class AMFChannelExt extends AMFChannel {
		
		public function AMFChannelExt( id:String = null, uri:String = null ) {
			
			var url:String = UrlUtils.applicationUrl;
			
			if (URLUtil.getProtocol(url) == 'file'){
				//url = "http://localhost/" + url.substr(url.indexOf("telenet")).replace(/\\/g, '/');
				url = "http://localhost/telenet/api/api_general.php";
			}
			url = url.replace( /\/flex.*/, "/api/api_general.php");

			super( "my-channel", url );
		}
		
		public function remoteLog( logs:Array ) : void {
			
			RemoteLogDispacher.instance.dispatchEvent( new RemoteLogEvent( logs ) );
		}
		
	}
}