<?php
/**
 * This is the class used by the other classes related to sending sms for creation
 * of the xml files which are sended to mobitels server. The benefit is it does not
 * use a php extension.
 *
 *	<code>
 *	<?php
 *		$xml_file = new xml_child('root');
 *		$xml_file->addAttribute(array('version' => '1.0')); // it only accepts array.
 *		$xml_header = xml_child::addFastNode('header', 'The content for header'); // gives a new instance of the xml_child object.
 *		$xml_file->addNode($xml_header); // new we added the new node to the root node.
 *		$xml_body = xml_child::addFastNode('body', ''); // this node will be a new node in the root node.
 *		$xml_item_0 = xml_child::addFastNode('item', 'The item 1', array('id' => '1')); // we add child nodes to the body node.
 *		$xml_item_1 = xml_child::addFastNode('item', 'The item 2', array('id' => '2')); // another child node for body.
 *		$xml_body->addNode(array($xml_item_0, $xml_item_1)); // if you want to add more nodes at once give each node like an array element.
 *		$xml_file->addNode($xml_body); // now we added the body node with its children to the root node
 *		$xml_file->renderNode($some_string); // $some_string will include the text data of the created xml;
 *		echo $some_string; // will print the xml text.
 *	?>
 *	</code>
 *	This will out put:
 *	<code>
 *	<root version="1.0">
 *	 	<header>The content for header</header>
 *	 	<body>
 *	 		<item id="2">The item 2</item>
 *	 		<item id="2">The item 2</item>
 *	 	</body>
 *	</root>
 *	</code>
 *
 * @package		MvrataUtils
 * @author		Silvester Maraz <silvester@odiseja.com>
 */
class xml_child {

	var $xml_node = array('starttag' => '', 'endtag' => '', 'data' => '');
	var $attributes = array();
	var $childs = array();
	var $name;


	/**
	* @return void
	* @param string $name name of the node
	* @desc creates a new node. Constructor
	*/
	function xml_child($name){
		
		$this->name = $name;
		$this->startTag();
		$this->endTag();
	}


	/**
	* @access public
	* @return object
	* @param string $name The name of the node. <name></name>
	* @param string $data The data for the node. <name>data</name>
	* @param array $attribute The attributes for the node <name att="1">data</name>
	* @desc Creates a new xml_child object
	*/
	function addFastNode($name, $data, $attribute = ''){
		
		$obj = new xml_child($name);
		
		$obj->addData($data);
		
		$obj->addAttribute($attribute);
		
		return $obj;	
		
	}


   /**
	* @access private
	* @return void
	* @desc Generates the starting tag.
	*/
	function startTag(){
		$this->xml_node['starttag'] = '<'.$this->name.'>';
	}


	/**
	* @access private
	* @return void
	* @desc Generates the ending tag.
	*/
	function endTag(){
		$this->xml_node['endtag'] = '</'.$this->name.'>';
	}


	/**
	* @access public
	* @return void
	* @param string $data the data for the xml node
	* @desc Adds data to the xml node.
	*/
	function addData($data){
		//$this->xml_node['data'] = utf8_encode($data);
		$this->xml_node['data'] = $data;
	}


	/**
	* @access private
	* @return void
	* @param string $att
	* @desc Rebuilds the starting tag
	*/
	function rebuildStartTag($att){
		if($att){
			$this->xml_node['starttag'] = '<'.$this->name.' '.$att.'>';
		};
	}


	/**
	* @access public
	* @return void
	* @param array $array An array with the attributes like array('encoding' => 'iso-8859-2')
	* @desc Add attributes to the xml tag.
	*/
	function addAttribute($array){
		
		if(is_array($array)){
			
			foreach($array as $key => $value){
				$this->attributes[$key] = $value;			
			};
			
			$att = $this->generateAttribute();
			
			$this->rebuildStartTag($att);
			
		};
	}


	/**
	* @access private
	* @return string if the attributes are not set returns FALSE.
	* @desc Implodes the attributes into a text.
	*/
	function generateAttribute(){
		
		if(count($this->attributes) > 0){
			
			foreach ($this->attributes as $key => $value){
				$attributes[] =  "$key=\"$value\"";
			};
			$att = implode(' ', $attributes);
			
			return $att;
		
		} else {
			
			return FALSE;
			
		}
	}


	/**
	* @access public
	* @return void
	* @param object $node The xml_child object to add
	* @desc Adds a new child node to the current node.
	*/
	function addNode($node){
		
		if(is_object($node)){
			array_push($this->childs, &$node);
			
			if(strlen($this->xml_node['data']) > 0)
				trigger_error('If you have child nodes the data in your node won\'t be shown!!!', E_USER_WARNING);

		} elseif(is_array($node)){
			foreach($node as $obj){
				if(is_object($obj))
					array_push($this->childs, $obj);
			};
		};
	}


	/**
	* @access public
	* @return void Everything is returned in the given variable
	* @param int $start_tab Where to start with the tabs for indenting the xml
	* @param string $str The string which will keep the text for the xml
	* @desc Renders the xml text to the given variable
	*/
	function renderNode(&$str, $start_tab = 0){
		
		$str .= add_tab($start_tab).$this->xml_node['starttag'];
		
		if(count($this->childs) > 0){
			foreach ($this->childs as $object){
				$str .= "\n";
				$start_tab++;
				$object->renderNode($str, $start_tab);
				$start_tab--;
			}
			
			$str .= "\n";
			$str .= add_tab($start_tab).$this->xml_node['endtag'];
		
		} else {
			
			$str.= $this->xml_node['data'].$this->xml_node['endtag'];
			
		}
	}
}


/**
 * This function is used for counting and generating tabs for the xml file. (XML Beautifier).
 *
 * @param int $number
 * @return string
 */
function add_tab($number){
	$str = '';
	
	for($x = 0; $x < $number; $x++){
		$str .= "\t";
	};
	return $str;
}
?>