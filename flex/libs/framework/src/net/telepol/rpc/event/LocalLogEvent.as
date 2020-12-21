package net.telepol.rpc.event
{
	import flash.events.Event;
	
	public class LocalLogEvent extends Event
	{
		public static const LOCAL_LOG:String = "localLog";
		
		protected var _message:String;
		protected var _level:int = 0;
		protected var _category:String;
		protected var _time:Number;
		
		public function LocalLogEvent( message:String, level:int, category:String, time:Number ) {
			super( LOCAL_LOG );
			_message = message;
			_level = level;
			_category = category;
			_time = new Date().getTime();
		}
		
		public function get message() : String {
			return _message;
		}
		
		public function get level() : int {
			return _level;
		}
		
		public function get category() : String {
			return _category;
		}
		
		public function get time() : Number {
			return _time;
		}
		
	}
}