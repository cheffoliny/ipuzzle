<?php
	class DBMailInvoice
		extends DBBase2 {
			
		public function __construct() {
			global $db_finance;
			//$db_sod->debug=true;
			
			parent::__construct($db_finance, 'invoice_massmail_settings');
			//$db_personnel->debug()=true;
		}
		
		public function getMailSettings() {
			
			$sQuery = "
				SELECT 
					id,
					subject,
					text,
					email_from,
					email_reply,
					ftp_user,
					ftp_host,
					ftp_password
				FROM invoice_massmail_settings 
				WHERE to_arc = 0 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		function myftp_connect($aftp) {
			if ( empty($aftp) ) {
				return false;
			}
	
			$ftp_con_id = ftp_connect($aftp['host']);
			
			if ( !$ftp_con_id ) {
				return false;
			}
	
			if ( ftp_get_option($ftp_con_id, FTP_TIMEOUT_SEC) < $aftp['set_timeout_sec'] ) {
				ftp_set_option($ftp_con_id, FTP_TIMEOUT_SEC, $aftp['set_timeout_sec']);
			}
	
			$result = ftp_login($ftp_con_id, $aftp['user'], $aftp['pass']);
			
			if ( !$result ) {
				return false;
			}
	
			if ( $aftp['passive'] ) {
				$result = ftp_pasv( $ftp_con_id, true );
				
				if ( !$result ) {
					APILog::Log(1, "Не може да се влезе в пасивен режим!", __FILE__, __LINE__);
				}
			}
	
			$result = ftp_chdir( $ftp_con_id, $aftp['chdir'] );
			
			if ( !$result ) {
				APILog::Log(1, "Не може да бъде направена връзка с FTP директорията {$aftp['chdir']}", __FILE__, __LINE__);
				
				throw new Exception("Не може да бъде направена връзка с FTP директорията {$aftp['chdir']}!", DBAPI_ERR_INVALID_PARAM);

				return false;
			}
			//ako sme tuk vsichko e 6 i vrashtam ftp connection id
			return $ftp_con_id;
		}		
		
		function myftp_disconnect($ftp_con_id) {
			return ftp_close( $ftp_con_id );
		}
	
		public function getClientByInvoice( $invoice ) {
			global $db_finance, $db_name_finance, $db_name_sod;
			
			$invoice 	= is_numeric($invoice) && strlen($invoice) == 13 ? $invoice : 0;
			$aData		= array();
			
			$table 		= "sales_docs_".substr($invoice, 0, 6);
			$tables 	= SQL_get_tables($db_finance, 'sales_docs_', '______');
			
			if ( in_array($table, $tables) ) {			
				
				$sQuery = "
					SELECT 
						sd.id,
						cl.name as client,
						cl.invoice_email as email,
						cl.id as id_client
					FROM {$db_name_finance}.{$table} sd
					LEFT JOIN {$db_name_sod}.clients cl ON cl.id = sd.id_client
					WHERE sd.to_arc = 0 	
						AND sd.id = '{$invoice}'			
				";
				
				$aData = $this->selectOnce( $sQuery );
			}
			
			return $aData;
		}	

		function sendMail( $to, $from, $reply, $subject, $body, $file, $filename ) {
	
			$random_hash 	= md5(date('r', time()));
			$headers 		= "From: {$from}\r\nReply-To: {$reply}";
			$headers 		.= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
			$attachment 	= chunk_split( base64_encode(file_get_contents($file)) );
			$textbody 		= chunk_split( base64_encode($body) );
			ob_start(); 
?>
--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: base64

<?php echo $textbody; ?>

--PHP-alt-<?php echo $random_hash; ?>--

--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: application/zip; name="<?php echo $filename; ?>" 
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

<?php echo $attachment; ?>
--PHP-mixed-<?php echo $random_hash; ?>--
	
<?php
			$message = ob_get_clean();
			$mail_sent = @mail( $to, $subject, $message, $headers, "-f{$from}" );
			
			return $mail_sent;
		}
		
		function refresh($files, DBResponse $oResponse) {
			$oResponse->setData($files);
			
			$oValidate = new Validate();
			
			foreach ( $oResponse->oResult->aData as $key => &$aRow ) {
				$oResponse->setDataAttributes( $key, 'file_date', array("style" => "width: 120px; text-align: center;") );
						
				$invoice = explode(".", $aRow['file_name']);
				
				if ( isset($invoice[0]) ) {
					$inv = $this->getClientByInvoice($invoice[0]);
					$email = isset($inv['email']) && !empty($inv['email']) ? $inv['email'] : "noemail";
					$aRow['client'] = isset($inv['client']) ? $inv['client'] : "";
					$oValidate->variable = $email;
					$oValidate->checkEMAIL();
				
					if ( $oValidate->result ) {
						$aRow['email'] = $email;
					} else {
						$aRow['email'] = "НЕВАЛИДЕН!";
						//$aRow['email'] = $email;
						
						$oResponse->setDataAttributes( $key, 'file_date', array("style" => "width: 120px; text-align: center; background: #FFE8E8;") );
						$oResponse->setDataAttributes( $key, 'file_name', array("style" => "background: #FFE8E8;") );
						$oResponse->setDataAttributes( $key, 'email', array("style" => "background: #FFE8E8;") );
						$oResponse->setDataAttributes( $key, 'client', array("style" => "background: #FFE8E8;") );
					}
				} else {
					$aRow['client'] = "";
					$aRow['email'] = "НЕВАЛИДЕН!";
						
					$oResponse->setDataAttributes( $key, 'file_date', array("style" => "width: 120px; text-align: center; background: #FFE8E8;") );
					$oResponse->setDataAttributes( $key, 'file_name', array("style" => "background: #FFE8E8;") );
					$oResponse->setDataAttributes( $key, 'email', array("style" => "background: #FFE8E8;") );
					$oResponse->setDataAttributes( $key, 'client', array("style" => "background: #FFE8E8;") );					
				}
			}
			
			$oResponse->setField("file_name", "файл", "файл");
			$oResponse->setField("client", "Клиент", "Клиент");
			$oResponse->setField("email", "Имейл", "Имейл");
			$oResponse->setField("file_date", "последна промяна", "последна промяна");
		}		
		
		function GetFtpFiles($ftp_con_id) {
			if ( !isset($ftp_con_id) ) {
				APILog::Log(1, "ivalid ftp connection", __FILE__, __LINE__);
				return false;
			}
	
			$dir_contents = array();
			$files = array();
	
			$dir_contents = ftp_nlist( $ftp_con_id, "-1" );
			if ( $dir_contents == false ) {
				return false;
			}
	
			foreach( $dir_contents as $key => $value ) {
				//ako elementat ne e s extention .pdf /ci/ ne se dobavia kam masiva
				if ( strcasecmp(substr($value, strlen($value)-4), ".pdf") ) {
					continue;
				}
	
				$result = ftp_mdtm($ftp_con_id, $value);
				
				if ( $result == -1 ) {
					continue;
				}
		
				$files[$key] = array( 'file_name' => $value, 'file_date' => date("d.m.Y H:i:s", $result) );
			}
	
			return $files;
		}		
		
		function onMailAction( $aFiles, $ftp_con_id, $aInvoice, DBResponse $oResponse ) {
			$oValidate = new Validate();
			
			if ( !file_exists("../storage/invoices/") ) {
				if ( @mkdir("../storage/invoices/", 0777) === FALSE) {
					throw new Exception("Грешка при създаване на директория!", DBAPI_ERR_INVALID_PARAM);
				}
			}			
			
			if ( empty($aFiles) ) {
				return false;
			}
	
			$sent_count = 0;
			$post_count = 0;
			$all_count  = count($aFiles);
	
			foreach ($aFiles as $key => &$value ) {
				$invoice = explode(".", $value['file_name']);
				
				if ( isset($invoice[0]) ) {
					$inv 				= $this->getClientByInvoice($invoice[0]);
					$value['client'] 	= isset($inv['client']) ? $inv['client'] : "";
					$email 				= isset($inv['email']) ? $inv['email'] : "noemail";
					$value['id_client'] = isset($inv['id_client']) && is_numeric($inv['id_client']) ? $inv['id_client'] : 0;
 					
					$oValidate->variable = $email;
					$oValidate->checkEMAIL();
				
					if ( $oValidate->result ) {
						$value['email'] = $email;
					} else {
						unset($aFiles[$key]);	
						continue;	
					}
				} else {
					unset($aFiles[$key]);
					continue;
				}				
	
				if ( !empty($value['email']) ) {
					$bresult = ftp_get( $ftp_con_id, "../storage/invoices/".$value['file_name'], $value['file_name'], FTP_BINARY );
					
					if ( !$bresult ) {
						throw new Exception("Грешка при опит за четене на {$value['file_name']}!", DBAPI_ERR_INVALID_PARAM);
						//echo "ftp_get() failed".$value['file_name'];
						return false;
					}
	
					//tuk go sendvam i premestvam v dir sent na FTP servera
					$subject 		= $aInvoice['subject']." ".date("m.Y");
					$email_from 	= $aInvoice['email_from'];
					$email_reply 	= $aInvoice['email_reply'];
					$email_text		= $aInvoice['text'];
					
					$result = $this->sendMail($value['email'], $email_from, $email_reply, $subject, $email_text, "../storage/invoices/".$value['file_name'], $value['file_name']);
					
					$aMailData = array();
					$aMailData['id_client'] 	= $value['id_client'];
					$aMailData['client_name'] 	= $value['client'];
					$aMailData['invoice_email'] = $value['email'];
					$aMailData['file_name'] 	= $value['file_name'];
					$aMailData['mail_result'] 	= empty($result) ? 0 : 1;
					$aMailData['updated_time']  = time();
					$aMailData['updated_user']  = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;		
					$aMailData['to_arc']		= 0;		
			
					if ( $this->setMailHistoryRecord($aMailData) != DBAPI_ERR_SUCCESS ) {
						APILog::Log(4, "ERR", __FILE__, __LINE__);
					}
		
					unlink("../storage/invoices/".$value['file_name']);
					
					if ( !$result ) {
						continue;
					}
					
					ftp_rename( $ftp_con_id, $value['file_name'], "sent/".$value['file_name'] );
		
					$sent_count++;
				}
			}
	
			$oResponse->setAlert( "общо файлове: {$all_count}\r\nизпратени по e-mail и преместени в sent: {$sent_count}\nнеобработени файлове: ".($all_count-$sent_count) );
		}
		
		public function setMailHistoryRecord( $aMailData ) {
			global $db_finance, $db_name_finance;
			
			$oRes = $db_finance->Execute("SELECT * FROM {$db_name_finance}.invoice_massmail_history WHERE 0");
			
			if ( !$oRes ) {
				return DBAPI_ERR_SQL_QUERY;
			}
			
			$sQuery = $db_finance->GetInsertSQL($oRes, $aMailData);
			
			$db_finance->Execute( $sQuery );
			
			return DBAPI_ERR_SUCCESS;
		}		
		
		function getHistory( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance;
	
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					id,
					id_client,
					client_name,
					invoice_email,
					file_name,
					mail_result,
					DATE_FORMAT(updated_time, '%d.%m.%Y %H:%i:%s') as dtime
				FROM {$db_name_finance}.invoice_massmail_history 
				WHERE to_arc = 0
			";
	
			$nPage 		= Params::get("current_page", 1);
			$sSortField = Params::get("sfield", "dtime");
			$nSortType	= Params::get("stype", "DBAPI_SORT_ASC");
					
			if ( empty($sSortField) ) {
				$sSortField = "dtime";
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";
			
			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);
			
			if ( $bLimited ) {
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}
				
							
			$oRS = $db_finance->execute( $sQuery );	
			
			if ( $oRS ) {
				$oData = $oRS->getArray();
			} else $oData = array();
			
			$oRes = $db_finance->Execute("SELECT FOUND_ROWS()");
	
			$nRowTotal = current( $oRes->FetchRow() );

			$oResponse->setPaging(
				$nRowCount,
				$nRowTotal,
				ceil($nRowOffset / $nRowCount) + 1
			);
	
			$oResponse->setData( $oData );
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['mail_result'] = $val['mail_result'] == 1 ? 'Изпратен' : 'Неизпратен';
				
				$oResponse->setDataAttributes( $key, 'dtime', array('style' => 'text-align: center; width: 120px;') );	
				$oResponse->setDataAttributes( $key, 'client_name', array('style' => 'text-align: left;') );	
				$oResponse->setDataAttributes( $key, 'invoice_email', array('style' => 'text-align: left; width: 180px;') );	
				$oResponse->setDataAttributes( $key, 'file_name', array('style' => 'text-align: left; width: 150px;') );
				$oResponse->setDataAttributes( $key, 'mail_result', array('style' => 'text-align: left; width: 120px;') );	
			}	
						
			$oResponse->setField("dtime", "Време", 	"Сортирай по време");
			$oResponse->setField("client_name", 	"Клиент", NULL);
			$oResponse->setField("invoice_email", 	"e-mail", NULL);
			$oResponse->setField("file_name", 		"Файл",   NULL);
			$oResponse->setField("mail_result", 	"Статус", NULL);
			
		}	
	}
?>