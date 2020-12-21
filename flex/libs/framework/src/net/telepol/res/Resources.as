package net.telepol.res
{
	public class Resources 
	{
		[Embed(source="img/debug.png")]
		public var ImgLevelDebug:Class;
		[Embed(source="img/info.png")]
		public var ImgLevelInfo:Class;
		[Embed(source="img/warning.png")]
		public var ImgLevelWarning:Class;
		[Embed(source="img/error.png")]
		public var ImgLevelError:Class;
		[Embed(source="img/fatal.png")]
		public var ImgLevelFatal:Class;
		
		[Bindable]
		[Embed(source="img/open.png")]
		public var ImgFileOpen:Class;
		[Bindable]
		[Embed(source="img/save.png")]
		public var ImgFileSave:Class;
		[Bindable]
		[Embed(source="img/delete.png")]
		public var ImgFileDelete:Class;
		[Bindable]
		[Embed(source="img/close.png")]
		public var ImgFileClose:Class;
		
		[Embed(source="img/bullet_add.png")] [Bindable] public var imgBulletAddClass:Class;
		[Embed(source="img/bullet_delete.png")] [Bindable] public var imgBulletDelClass:Class;
		
		private static var _instance:Resources = new Resources();
		
		public function Resources()  {
			
			if( _instance != null ) {
				throw new Error("Singleton object Resources can be accessed through Resources.instance");
			}
		}
		
		public static function get instance() : Resources {
			
			return _instance;	
		}

	}
}