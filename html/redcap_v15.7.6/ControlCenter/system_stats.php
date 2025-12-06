<?php


include 'header.php';
if (!ACCESS_ADMIN_DASHBOARDS) redirect(APP_PATH_WEBROOT);
?>

<h4 style="margin-top: 0;"><i class="fas fa-table" style="margin-left:1px;margin-right:1px;"></i> <?php echo $lang['dashboard_48'] ?></h4>

<div id='controlcenter_stats' style='width:95%;max-width:420px;'>
	<img src="<?php echo APP_PATH_IMAGES ?>progress_circle.gif">
	<b><?php echo $lang['dashboard_01'] ?>...</b>
</div>

<script type="text/javascript">
// Chain all ajax events so that they are fired sequentially
var ccstats  = app_path_webroot + 'ControlCenter/stats_ajax.php';
$(function() {
	// Statistics table
	$.get(ccstats, {}, function(data) {
		// Create table on page
		$('#controlcenter_stats').html(data);
		// Multiple ajax calls for stats that take a long time
		$.get(ccstats, { logged_events: 1}, function(data) {
			var le = data.split("|");
			$('#logged_events_30min').html(le[0]);
			$('#logged_events_today').html(le[1]);
			$('#logged_events_week').html(le[2]);
			$('#logged_events_month').html(le[3]);
		} );
		setTimeout(function(){
			$.get(ccstats, { total_fields: 1}, function(data) { $('#total_fields').html(data); } );
			$.get(ccstats, { mysql_space: 1}, function(data) { $('#mysql_space').html(data); } );
			$.get(ccstats, { webserver_space: 1}, function(data) { $('#webserver_space').html(data); } );
		},100);
		setTimeout(function(){
			$.get(ccstats, { survey_participants: 1}, function(data) { $('#survey_participants').html(data); } );
			$.get(ccstats, { survey_invitations: 1}, function(data) {
				var si = data.split("|");
				$('#survey_invitations_sent').html(si[0]);
				$('#survey_invitations_responded').html(si[1]);
				$('#survey_invitations_unresponded').html(si[2]);
			} );
		},200);
		setTimeout(function(){
		    if ($('#total_data_mart_projects').length) {
                $.get(ccstats, {data_mart: 1}, function (data) {
                    var le = data.split("|");
                    $('#total_data_mart_projects').html(le[0]);
                    $('#total_data_mart_records_imported').html(le[1]);
                    $('#total_data_mart_values_imported').html(le[2]);
                });
            }
		    if ($('#total_ddp_fhir_projects_adjudicated').length) {
                $.get(ccstats, {ddp1: 1, fhir: 1}, function (data) {
                    var le = data.split("|");
                    $('#total_ddp_fhir_values_adjudicated').html(le[0]);
                    $('#total_ddp_fhir_records_imported').html(le[1]);
                    $('#total_ddp_fhir_projects_adjudicated').html(le[2]);
                });
            }
		},400);
	} );
});
function getTotalLogEventCount() {
	$('#logged_events').html('<span style="color:#999;"><?php echo $lang['dashboard_39'] ?>...</span>');
	$.get(ccstats, { logged_events_total: 1}, function(data) { $('#logged_events').html(data); } );
}
function getDdpCounts1() {
    $('#total_ddp_values_pulled').html('<span style="color:#999;"><?php echo $lang['dashboard_39'] ?>...</span>');
    $.get(ccstats, {total_ddp_values_pulled: 1}, function(data){ $('#total_ddp_values_pulled').html(data); });
}
function getDdpCounts2() {
    $('#total_ddp_records_imported').html('<span style="color:#999;"><?php echo $lang['dashboard_39'] ?>...</span>');
    $.get(ccstats, {total_ddp_values_pulled: 1}, function(data){ $.get(ccstats, {ddp2: 1}, function (data) { $('#total_ddp_records_imported').html(data); }); });
}
function getDdpCounts3() {
    $('#total_ddp_values_adjudicated').html('<span style="color:#999;"><?php echo $lang['dashboard_39'] ?>...</span>');
    $('#total_ddp_projects_adjudicated').html('<span style="color:#999;"><?php echo $lang['dashboard_39'] ?>...</span>');
    $.get(ccstats, {ddp1: 1}, function (data) {
        var le = data.split("|");
        $('#total_ddp_values_adjudicated').html(le[0]);
        $('#total_ddp_projects_adjudicated').html(le[1]);
    });
}

function downloadStatsCsv() {
  let csv = [];
  // Add header row
  csv.push(["Label","Value"].join(","));

  $("#table-controlcenter_stats_inner tr:visible").each(function(){
    let cols = $(this).find("td:visible");
    if(cols.length >= 2){
      // Clean label: strip links, hidden spans/divs, bullets, extra spaces
      let label = $(cols[0]).clone()
        .find("a, span[style*='display:none'], span[style*='visibility:hidden'], div[style*='display:none'], div[style*='visibility:hidden']")
        .remove().end()
        .text()
        .replace(/•/g, "")              // remove bullet chars
        .replace(/\s+/g, " ")           // collapse spaces
        .trim();

      // Prepend space if label starts with a dash
      if(label.startsWith("-")) {
        label = " " + label;
      }

      // Clean value
      let value = $(cols[1]).clone()
        .find("a, span[style*='display:none'], span[style*='visibility:hidden'], div[style*='display:none'], div[style*='visibility:hidden']")
        .remove().end()
        .text()
        .replace(/•/g, "")
        .replace(/\s+/g, " ")
        .trim();

      // Always two columns, even if value is empty
      if(label.length > 0 || value.length > 0){
        // Escape quotes for CSV safety
        label = '"' + label.replace(/"/g,'""') + '"';
        value = '"' + value.replace(/"/g,'""') + '"';
        csv.push([label,value].join(","));
      }
    }
  });

  // Convert array to CSV string
  let csvString = csv.join("\n");
  let ts = now.replace(/ /g, "_").replace(/:/g, "").slice(0, -2);

  // Create downloadable link
  let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
  let link = document.createElement("a");
  let url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute("download", "redcap_system_statistics_"+ts+".csv");
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

<?php include 'footer.php';