package net.telepol.rpc.event
{
	import flash.events.Event;
	
	public class RemoteLogEvent extends Event
	{
		public static const REMOTE_LOG:String = "remoteLog";
		
		private var _remoteLog:Array;
		
		
		public function RemoteLogEvent( logs:Array ) {
			super( REMOTE_LOG );
			_remoteLog = logs;
		}
		
		public function get remoteLog() : Array {
			return _remoteLog;
		}
		
	}
}