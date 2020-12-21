<?php
	require_once("db_include.inc.php");

	define(
		'TRIGGER_AFTER_UPDATE_LOADINGS', 
		"
		CREATE TRIGGER <db>.trigger_after_update_loadings_origin AFTER UPDATE ON <db>.loadings_origin
		FOR EACH ROW 
		
		BEGIN
		
		IF OLD.direction_office_to <> NEW.direction_office_to THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'direction_office_to', OLD.direction_office_to, NEW.direction_office_to, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_name <> NEW.receiver_name THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_name', OLD.receiver_name, NEW.receiver_name, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_city <> NEW.receiver_city THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_city', OLD.receiver_city, NEW.receiver_city, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_quarter <> NEW.receiver_quarter THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_quarter', OLD.receiver_quarter, NEW.receiver_quarter, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_street <> NEW.receiver_street THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_street', OLD.receiver_street, NEW.receiver_street, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_street_num <> NEW.receiver_street_num THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_street_num', OLD.receiver_street_num, NEW.receiver_street_num, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_address_other <> NEW.receiver_address_other THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_address_other', OLD.receiver_address_other, NEW.receiver_address_other, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_phone <> NEW.receiver_phone THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_phone', OLD.receiver_phone, NEW.receiver_phone, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_face <> NEW.receiver_face THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_face', OLD.receiver_face, NEW.receiver_face, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.receiver_zone <> NEW.receiver_zone THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'receiver_zone', OLD.receiver_zone, NEW.receiver_zone, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_type <> NEW.shipment_type THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_type', OLD.shipment_type, NEW.shipment_type, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_description <> NEW.shipment_description THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_description', OLD.shipment_description, NEW.shipment_description, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_pack_count <> NEW.shipment_pack_count THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_pack_count', OLD.shipment_pack_count, NEW.shipment_pack_count, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_pack_dimensions_l <> NEW.shipment_pack_dimensions_l THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_pack_dimensions_l', OLD.shipment_pack_dimensions_l, NEW.shipment_pack_dimensions_l, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_pack_dimensions_w <> NEW.shipment_pack_dimensions_w THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_pack_dimensions_w', OLD.shipment_pack_dimensions_w, NEW.shipment_pack_dimensions_w, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_pack_dimensions_h <> NEW.shipment_pack_dimensions_h THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_pack_dimensions_h', OLD.shipment_pack_dimensions_h, NEW.shipment_pack_dimensions_h, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.shipment_pack_weight <> NEW.shipment_pack_weight THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'shipment_pack_weight', OLD.shipment_pack_weight, NEW.shipment_pack_weight, NEW.updated_user, NOW());
		END IF;
		
		/* TODO: ?????????? */
		
		IF OLD.holiday_delivery_date <> NEW.holiday_delivery_date THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'holiday_delivery_date', OLD.holiday_delivery_date, NEW.holiday_delivery_date, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.tariff_code <> NEW.tariff_code THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'tariff_code', OLD.tariff_code, NEW.tariff_code, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.tariff_code3 <> NEW.tariff_code3 THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'tariff_code3', OLD.tariff_code3, NEW.tariff_code3, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.tariff_code8 <> NEW.tariff_code8 THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'tariff_code8', OLD.tariff_code8, NEW.tariff_code8, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.note <> NEW.note THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'note', OLD.note, NEW.note, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.payment_client <> NEW.payment_client THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'payment_client', OLD.payment_client, NEW.payment_client, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.payment_side <> NEW.payment_side THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'payment_side', OLD.payment_side, NEW.payment_side, NEW.updated_user, NOW());
		END IF;
		
		IF OLD.courier_price <> NEW.courier_price THEN
		INSERT INTO loadings_history_origin(id_loading, field, old_value, new_value, updated_user, updated_time)
		VALUES(NEW.id, 'courier_price', OLD.courier_price, NEW.courier_price, NEW.updated_user, NOW());
		END IF;
		
		/* ????? ?? ?????? ?????? ? ?????????? ?????? ????? ?????? */
		
		END
		");
	
	define(
		'TRIGGER_AFTER_INSERT_LOADINGS_SERVICES', 
		"
		CREATE TRIGGER <db>.trigger_after_insert_loadings_services_origin AFTER INSERT ON <db>.loadings_services_origin
		FOR EACH ROW 
		
		BEGIN
		
		INSERT INTO loadings_services_history_origin
		SET 
		action             = 'insert',
		id_loading_service = NEW.id,
		id_loading         = NEW.id_loading,
		id_pay_office      = NEW.id_pay_office,
		id_pay_client      = NEW.id_pay_client,
		pay_side           = NEW.pay_side,
		service_type       = NEW.service_type,
		description        = NEW.description,
		count              = NEW.count,
		id_measure         = NEW.id_measure,
		sum                = NEW.sum,
		total_sum          = NEW.total_sum,
		currency           = NEW.currency,
		CD_get_office      = NEW.CD_get_office,
		CD_send_office     = NEW.CD_send_office,
		CD_type            = NEW.CD_type,
		P_time             = NEW.P_time,
		P_type             = NEW.P_type,
		E_bus_reg_num      = NEW.E_bus_reg_num,
		E_description      = NEW.E_description,
		updated_user       = NEW.updated_user,
		updated_time       = NEW.updated_time,
		to_arc             = NEW.to_arc;
		
		UPDATE loadings_origin AS a
		SET _total_sum = (
		   SELECT SUM( b.total_sum )
		   FROM loadings_services_origin AS b
		   WHERE b.to_arc = 0
		   AND b.id_loading = a.id
		   )
		WHERE a.id = NEW.id_loading;
		
		END
		");
	
	define(
		'TRIGGER_AFTER_UPDATE_LOADINGS_SERVICES',
		"
		CREATE TRIGGER <db>.trigger_after_update_loadings_services_origin AFTER UPDATE ON <db>.loadings_services_origin
		FOR EACH ROW 
		BEGIN
		
		IF OLD.id_pay_office  <> NEW.id_pay_office
		OR OLD.id_pay_client  <> NEW.id_pay_client
		OR OLD.pay_side       <> NEW.pay_side
		OR OLD.service_type   <> NEW.service_type
		OR OLD.description    <> NEW.description
		OR OLD.count          <> NEW.count
		OR OLD.id_measure     <> NEW.id_measure
		OR OLD.sum            <> NEW.sum
		OR OLD.total_sum      <> NEW.total_sum
		OR OLD.currency       <> NEW.currency
		OR OLD.CD_get_office  <> NEW.CD_get_office
		OR OLD.CD_send_office <> NEW.CD_send_office
		OR OLD.CD_type        <> NEW.CD_type
		OR OLD.P_time         <> NEW.P_time
		OR OLD.P_type         <> NEW.P_type
		OR OLD.E_bus_reg_num  <> NEW.E_bus_reg_num
		OR OLD.E_description  <> NEW.E_description
		OR OLD.updated_user   <> NEW.updated_user
		OR OLD.updated_time   <> NEW.updated_time
		OR OLD.to_arc         <> NEW.to_arc
		THEN
		INSERT INTO loadings_services_history_origin
		SET 
		action             = IF(NEW.to_arc <> 0, 'delete', 'update'),
		id_loading_service = NEW.id,
		id_loading         = NEW.id_loading,
		id_pay_office      = NEW.id_pay_office,
		id_pay_client      = NEW.id_pay_client,
		pay_side           = NEW.pay_side,
		service_type       = NEW.service_type,
		description        = NEW.description,
		count              = NEW.count,
		id_measure         = NEW.id_measure,
		sum                = NEW.sum,
		total_sum          = NEW.total_sum,
		currency           = NEW.currency,
		CD_get_office      = NEW.CD_get_office,
		CD_send_office     = NEW.CD_send_office,
		CD_type            = NEW.CD_type,
		P_time             = NEW.P_time,
		P_type             = NEW.P_type,
		E_bus_reg_num      = NEW.E_bus_reg_num,
		E_description      = NEW.E_description,
		updated_user       = NEW.updated_user,
		updated_time       = NEW.updated_time,
		to_arc             = NEW.to_arc;
		END IF;
		
		UPDATE loadings_origin AS a
		SET _total_sum = (
		   SELECT SUM( b.total_sum )
		   FROM loadings_services_origin AS b
		   WHERE b.to_arc = 0
		   AND b.id_loading = a.id
		   )
		WHERE a.id = OLD.id_loading;
		
		END
		");
		
	define(
		'TRIGGER_AFTER_DELETE_LOADINGS_SERVICES', 
		"
		CREATE TRIGGER <db>.trigger_after_delete_loadings_services_origin AFTER DELETE ON <db>.loadings_services_origin
		FOR EACH ROW 
		
		BEGIN
		
		UPDATE loadings_origin AS a
		SET _total_sum = (
		   SELECT SUM( b.total_sum )
		   FROM loadings_services_origin AS b
		   WHERE b.to_arc = 0
		   AND b.id_loading = a.id
		   )
		WHERE a.id = OLD.id_loading;
		
		END
		");
		
	class DBTriggers
	{
		public static function getQueries( &$aQueries )
		{
			$aQueries = array(
				TRIGGER_AFTER_UPDATE_LOADINGS, 
				TRIGGER_AFTER_INSERT_LOADINGS_SERVICES, 
				TRIGGER_AFTER_UPDATE_LOADINGS_SERVICES,
				TRIGGER_AFTER_DELETE_LOADINGS_SERVICES
				);
				
			return DBAPI_ERR_SUCCESS;
		}
	}

?>