package net.telepol.log
{
	import flash.events.EventDispatcher;
	
	import mx.logging.ILogger;
	import mx.logging.Log;
	import mx.logging.LogEvent;
	
	import net.telepol.rpc.event.LocalLogEvent;

	public class LocalLogDispacher extends EventDispatcher
	{
		private static var _instance:LocalLogDispacher = new LocalLogDispacher();
		
		protected var logTarget:LogTarget = new LogTarget();
		
		
		public function LocalLogDispacher()  {
			super();
			if( _instance != null ) {
				throw new Error("Singleton object RemoteLogTarget can be accessed through LocalLogDispacher.instance");
			}
			logTarget.filters = ["*"];
			Log.addTarget( logTarget );
		}
		
		public static function get instance() : LocalLogDispacher {
			
			return _instance;
		}
		
		public static function logEvent( event:LogEvent ) : void {
			
			LocalLogDispacher.instance.dispatchEvent( new LocalLogEvent( event.message, event.level, ILogger( event.target ).category, new Date().getTime() ) );
		}
	}
}
