package net.telepol.log
{
	import mx.logging.AbstractTarget;
	import mx.logging.LogEvent;
	import net.telepol.log.LocalLogDispacher;	
	
	public class LogTarget extends AbstractTarget {
	
		public function LogTarget() {
			super();
		}
		
		override public function logEvent( event:LogEvent ) : void {
			
			LocalLogDispacher.logEvent( event );
		}
		
	}
}