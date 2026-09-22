<main class="ui-tech-planning-shell">
	<form name="form1" id="form1" onsubmit="return false;" class="ui-tech-planning-layout">
		<input type="hidden" name="start" id="start" value="0">
		<input type="hidden" name="end" id="end" value="0">
		<input type="hidden" name="date" id="date" value="0">
		<input type="hidden" name="id_request" id="id_request" value="0">
		<input type="hidden" name="id_request_office" id="id_request_office" value="0">

		<section class="ui-tech-planning-pane ui-tech-planning-schedule-pane">
			<iframe id="tech_planning_schedule" frameborder="0" src="page.php?page=tech_planning_schedule" title="Планиране"></iframe>
		</section>
		<section class="ui-tech-planning-pane ui-tech-planning-requests-pane">
			<iframe id="tech_plannig_requests" frameborder="0" src="page.php?page=tech_planning_requests" title="Задачи"></iframe>
		</section>
	</form>
</main>
