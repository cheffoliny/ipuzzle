<div class="container-fluid body-content h-100" >

	<form name="form1" id="form1" onsubmit="return false;" class="h-100">
		<input type="hidden" name="start" id="start" value="0">
		<input type="hidden" name="end" id="end" value="0">
		<input type="hidden" name="date" id="date" value="0">
		<input type="hidden" name="id_request" id="id_request" value="0">
		<input type="hidden" name="id_request_office" id="id_request_office" value="0">

		<div class="row justify-content-start h-50">
			<iframe id="tech_planning_schedule" class="h-100 w-100"  frameborder="0" src="page.php?page=tech_planning_schedule"></iframe>
		</div>
		<div class="row justify-content-start h-50">
			<iframe id="tech_plannig_requests" class="h-100 w-100" frameborder="0" src="page.php?page=tech_planning_requests"></iframe>
		</div>
	</form>
</div>