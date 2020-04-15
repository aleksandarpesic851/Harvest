<html>
	<head>
		<title> US Clinical Trials Feedback</title>
        <link rel="shortcut icon" href="/imgs/clinical_trial_icon.png">

		<!-- Bootstrap Datatable -->
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
		<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
	</head>
	
	<body>
		<div class="container">
			<h4 class="title" style="margin: 20px 0; text-align: center">Customers' Feedback</h4>
			<div class="row">
				<div class="col-12">
					<table id="feedback-table" class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>No</th>
								<th>Content</th>
								<th>Posted at</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</body>
	<script>
		$(document).ready(function() {
			initDatatable();
		} );

		function initDatatable() {
			let feedbackTable = $('#feedback-table').DataTable({
				"processing": true,
				"serverSide": true,
                "ajax": "/feedback/read.php",
                "columnDefs": [ {
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                } ],
				"columns": [
                    {data: null},
					{ data: "feedback" },
					{ data: "created_at" }
                ],
				"order": [[ 2, 'asc' ]]
			});
            feedbackTable.on( 'order.dt search.dt', function () {
                feedbackTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
		}
	</script>
</html>