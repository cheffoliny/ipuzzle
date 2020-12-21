package net.telepol.rpc {
	
	[RemoteClass(alias="FlexControl")]
	[Bindable]
	public class FlexControl {
		public var name:String;
		// array of FlexControlAttribute-s
		public var attributes:Array;
		
		public var defaultField:String;
		public var defaultValue:Object;
	}
}