package net.telepol.utils
{
	import mx.core.Application;
	import mx.managers.SystemManager;
	
	public class UrlUtils
	{
		private static var _url:String = null;
		
		public static function get applicationUrl() : String {
			
			if( _url == null ) {
				if( Application( Application.application ).url != null ) {
					_url = Application( Application.application ).url;
				}
				else {
					try {
						 _url = SystemManager.getSWFRoot( Application.application ).stage.loaderInfo.url;
					}
					catch( e:Error ) {}
				}
			} 
			
			return _url;
		}

	}
}