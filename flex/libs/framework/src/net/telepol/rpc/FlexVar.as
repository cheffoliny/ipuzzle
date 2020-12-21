package net.telepol.rpc {

	[RemoteClass(alias="FlexVar")]
	[Bindable]
	public class FlexVar {
		public function FlexVar(n:String="", v:Object=null) {
			name = n;
			value = v;
		}
		public var name:String;
		public var value:Object;
	}
}