<?php

require_once dirname(__DIR__) . '/api/api_tech_planning_schedule.php';

$schedule = new ApiTechPlanningSchedule();

if (
    $schedule->timeFormatToMinutes('67 ч.') !== 4020 ||
    $schedule->timeFormatToMinutes('01:30 ч.') !== 90 ||
    $schedule->timeFormatToMinutes('') !== 0 ||
    $schedule->planningSlotToTime(16) !== '08:00:00' ||
    $schedule->planningSlotToTime(35) !== '17:30:00' ||
    $schedule->planningSlotToTime(36) !== '18:00:00'
) {
    fwrite(STDERR, "Tech planning time conversion failed.\n");
    exit(1);
}

$requestsSource = file_get_contents(dirname(__DIR__) . '/db_api/DBTechRequests.class.php');
if (
    $requestsSource === false ||
    substr_count($requestsSource, 'COALESCE(tr.time, 0) AS timing') !== 2 ||
    strpos($requestsSource, "isset(\$val['timing']) && is_numeric(\$val['timing'])") === false
) {
    fwrite(STDERR, "Tech request timing normalization is incomplete.\n");
    exit(1);
}

echo "TECH_PLANNING_PHP85=PASS\n";
