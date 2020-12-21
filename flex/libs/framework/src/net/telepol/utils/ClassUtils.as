package net.telepol.utils
{
	import flash.utils.getQualifiedClassName;
	
	public class ClassUtils
	{
		public static function getShortClassName( o:Object ) : String {
			var className:String = getQualifiedClassName( o ); 
			if( className.indexOf("::") != -1 ) {
				className = className.split("::")[ 1 ]; 
			}
			return className;
		}
	}
}