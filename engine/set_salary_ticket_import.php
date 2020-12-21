<?php
	
	$nDate = mktime(0, 0, 0, date("m"), 1, date("Y"));
	
	
	$template->assign( 'year', date('Y', $nDate) );
	$template->assign( 'month', date('m', $nDate) );
	
	//var_dump($_FILES);
	//var_dump($_POST);

	if(!empty($_POST))
	{
		$nMonth = isset($_POST['month']) ? $_POST['month'] : '';
		$nYear 	= isset($_POST['year']) ? $_POST['year'] : '';
		
		if(empty($nMonth) || !( $nMonth>0 && $nMonth<13 ) ) {
			print("
				<script>
					alert('Въведете коректен месец');
				</script>
			");
		} elseif (empty($nYear) || !( $nYear>2000 && $nYear<2050 ) ) {
			print("
				<script>
					alert('Въведете коректна година');
				</script>
			");
			
		} elseif (empty($_FILES['pdf_ticket']['tmp_name']) ) {
						print("
				<script>
					alert('Грешка при импортиране на PDF-файл');
				</script>
			");
		} elseif (empty($_FILES['excel_file']['tmp_name']) ) {
						print("
				<script>
					alert('Грешка при импортиране на Excel-файл');
				</script>
			");
		} else {
			$nMonth = zero_padding($nMonth,2);
			
			$sTmpNamePDF  = $_FILES['pdf_ticket']['tmp_name'];
			$sFileTypePDF = $_FILES['pdf_ticket']['type'];
			$sFileNamePDF = $_SESSION['BASE_DIR'].'/storage/salary_ticket_'.$nYear.$nMonth.'.pdf';
			move_uploaded_file($sTmpNamePDF, $sFileNamePDF);
		
			$sTmpNameExcel  = $_FILES['excel_file']['tmp_name'];
			$sFileTypeExcel = $_FILES['excel_file']['type'];
			$sFileNameExcel = $_SESSION['BASE_DIR'].'/storage/salary_ticket_'.$nYear.$nMonth.'.xls';
			move_uploaded_file($sTmpNameExcel, $sFileNameExcel);
			
			print("
				<script>
					window.close();
				</script>
			");
		}
	}
?>