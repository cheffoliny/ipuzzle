<?php
	class pidfile {
		private $_file;

		public function __construct($dir, $name) {
			if ( !empty($dir) ) {
				$dir = $dir."/";
			}

			$this->_file = $dir.$name.".pid";

			$pid = getmypid();
			file_put_contents($this->_file, $pid);
		}

		public function __destruct() {
			if ( (!$this->_running) && file_exists($this->_file) ) {
				unlink($this->_file);
			}
		}

		public function is_running($pid) {
			exec("ps $pid", $ProcessState);

			if ( count($ProcessState) >= 2 ) {
				return true;
			} else {
				return false;
			}
			
		}
	}	
?>