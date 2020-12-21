<?
	$sql = "SELECT * FROM head_menu ORDER BY menu_order";
	$rs =$db_system->Execute($sql);

    $menu = array();
    $menu_start = array();
	$menu_structure='';
	if($rs && !$rs->EOF) {
		$menu_data = $rs->GetArray();

		APILog::Log(0,$menu_data);
		//main level
		foreach($menu_data as $key => $row)
		if((in_array($row['filename'],$_SESSION['userdata']['access_right_files'])) ||$row['parent_id']=='0')
			if(!$row['parent_id'])
			{
				$row['sublink'] = array();
				$menu[$row['id']] = $row;
				unset($menu_data[$key]);
			}

		// sublevel   1-
	   foreach($menu_data as $key => $row)
		if((in_array($row['filename'],$_SESSION['userdata']['access_right_files'])) || empty($row['filename']))
			if(isset($menu[$row['parent_id']])) 
			{
				$row['sublink'] = array();
				$menu[$row['parent_id']]['sublink'][$row['id']] = $row;
				unset($menu_data[$key]);
			}
		

		// sublevel  2-
		foreach($menu_data as $key => $row)
		if(in_array($row['filename'],$_SESSION['userdata']['access_right_files']))
			foreach($menu as $k => $v)
				foreach($v['sublink'] as $kk => $vv)
				if($row['parent_id'] == $kk) 
				{
					$row['sublink'] = array();
					$menu[$k]['sublink'][$kk]['sublink'][$row['id']] = $row;
				}

		foreach($menu as $key => $value)
		{
			foreach($value['sublink'] as $subkey => $subvalue)
				if( isset($subvalue['sublink']) && (count($subvalue['sublink'])==0) && empty($subvalue['filename']) )
				{
					unset($menu[$key]['sublink'][$subkey]);
				}

			if( count($value['sublink'])==0 )
			{
				unset( $menu[$key] );
			}
		}


	}
	

$menu_start = $menu;

foreach($menu_start as $k => $v) {
      $v['sublink'] = array_slice($v['sublink'], 0, 1);
      foreach($v['sublink']as $l => $val) {
          if($val['filename']){
              $menu_start[$k]['filename'] = $val['filename'];
          }elseif(!$val['filename'] && $val['sublink']){
              $val['sublink'] = array_slice($val['sublink'], 0, 1);
              foreach($val['sublink']as $s => $sv) {
                  $menu_start[$k]['filename'] = $sv['filename'];
              }
          }else{
               $menu_start[$k]['filename'] = "#";
          }
     }
}


$menu_start = array_slice($menu_start, 0, 1);
$menu_start['id']=$menu_start[0]['id'];
$menu_start['filename']=$menu_start[0]['filename'];
?>
