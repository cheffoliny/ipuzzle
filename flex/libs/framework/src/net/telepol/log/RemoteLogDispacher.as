package net.telepol.log
{
	import flash.events.EventDispatcher;
	import net.telepol.rpc.event.RemoteLogEvent;
	
	public class RemoteLogDispacher extends EventDispatcher
	{
		private static var _instance:RemoteLogDispacher = new RemoteLogDispacher();
		
		public function RemoteLogDispacher()  {
			super();
			if( _instance != null ) {
				throw new Error("Singleton object RemoteLogDispacher can be accessed through RemoteLogDispacher.instance");
			}
		}
		
		public static function get instance() : RemoteLogDispacher {
			
			return _instance;	
		}
		
		public static function logEvent( event:RemoteLogEvent ) : void {
			
			RemoteLogDispacher.instance.dispatchEvent( event );
		}
		

	}
}