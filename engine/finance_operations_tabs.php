<?php

if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
    if ( in_array('sales_docs', $_SESSION['userdata']['access_right_levels']) ) {
        $view['sales_docs'] = true;
    }

    if ( in_array('buy_docs', $_SESSION['userdata']['access_right_levels']) ) {
//        $edit['buy_docs'] = true;
        $view['buy_docs'] = true;
    }

    if ( in_array('currency_movement', $_SESSION['userdata']['access_right_levels']) ) {
        $view['currency_movement'] = true;
    }

    if ( in_array('view_pay_desks_reports', $_SESSION['userdata']['access_right_levels']) ) {
//        $edit['view_pay_desks_reports'] = true;
        $view['view_pay_desks_reports'] = true;
    }

    if ( in_array('view_firm_balances', $_SESSION['userdata']['access_right_levels']) ) {
        $view['view_firm_balances'] = true;
    }

    if ( in_array('view_balance', $_SESSION['userdata']['access_right_levels']) ) {
//        $edit['view_balance'] = true;
        $view['view_balance'] = true;
    }

    if ( in_array('view_money_nomenclatures_detail', $_SESSION['userdata']['access_right_levels']) ) {
        $view['view_money_nomenclatures_detail'] = true;
    }

    if ( in_array('summary_object_finances_main', $_SESSION['userdata']['access_right_levels']) ) {
        $view['summary_object_finances_main'] = true;
    }

    if ( in_array('online_payments', $_SESSION['userdata']['access_right_levels']) ) {
        $view['online_payments'] = true;
    }

    if ( in_array('view_money_nomenclatures_detail', $_SESSION['userdata']['access_right_levels']) ) {
        $view['view_money_nomenclatures_detail'] = true;
    }

}