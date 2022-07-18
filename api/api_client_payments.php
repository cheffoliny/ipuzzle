<?php

	class ApiClientPayments
	{
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset( $aParams['nID'] ) && !empty( $aParams['nID'] ) )
			{
				$oClients = new DBClients();
				
				$oClients->getPaymentsReport( $oResponse, $aParams );
			}
			
			$oResponse->printResponse( "Картон на клиента - Плащания", "clients_payments" );
		}

        public function openPDF(DBResponse $oResponse) {
            $nID    = Params::get('nIDInvoice', 0);
            //$sTable = "sales_docs_".substr($nID, 0, 6);

            $oDBSalesDocs   = new DBSalesDocs();
            $oSaleDocPDF    = new SaleDocPDF('P');
            $aSalesDocs     = $oDBSalesDocs->getDoc($nID);

            $oSaleDocPDF->copie = true;
            $oSaleDocPDF->PrintReport($nID, '', $aSalesDocs['view_type']);

            $oSaleDocPDF->Output();
        }

        public function signPDF(DBResponse $oResponse) {
            $nID    = Params::get('nIDInvoice', 0);
            //$sTable = "sales_docs_".substr($nID, 0, 6);

            $oDBSalesDocs   = new DBSalesDocs();
            //$oSaleDocPDF    = new SaleDocPDF('P');

            $aSalesDocs     = $oDBSalesDocs->getDoc($nID);
            if($aSalesDocs['version'] == 1) {
                $oSaleDocPDF    = new SaleDocPDF('P');
            } else {
                $oSaleDocPDF    = new InvoicePDF('P');
            }

            header('Content-type: text/json');
            echo $oSaleDocPDF->PrintReport($nID, '', $aSalesDocs['view_type'],0,true);
        }
		
	}

?>