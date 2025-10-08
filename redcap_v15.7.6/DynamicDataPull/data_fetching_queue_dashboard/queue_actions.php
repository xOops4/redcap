<?php
use Vanderbilt\REDCap\Classes\Utility\SessionDataUtils;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\QueueManager;

require_once dirname(__DIR__, 2) . '/Config/init_project.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}


try {
    $queueManager = new QueueManager($project_id);

    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'queue-selected':
            $record_ids = $_POST['record_ids'] ?? [];
            foreach ($record_ids as $record_id) {
                $queueManager->queueRecord($record_id);
            }
            break;
        case 'queue-all':
            $queueManager->queueAllRecords();
            break;
        
        default:
            # code...
            break;
    }
    SessionDataUtils::alert($lang['cdp_dashboard_alert_success'], 'info');
} catch(Throwable $th) {
    SessionDataUtils::alert($th->getMessage(), 'danger');
}finally {
    // Redirect back to referring page
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $referer");
    exit;
}

?>