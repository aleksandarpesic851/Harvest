<html>
	<head>
		<title> US Clinical Trials Condition Synomym</title>
        <link rel="shortcut icon" href="/imgs/clinical_trial_icon.png">

		<!-- Bootstrap Datatable -->
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
		<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
		<!-- Inline Editable -->
		<script src="SimpleTableCellEditor/SimpleTableCellEditor.es6.min.js"></script>
	</head>
	
	<body>
		<div class="container">
			<h4 class="title" style="margin: 20px 0; text-align: center">Manage Conditions Synonyms</h4>
			<div class="row">
				<div class="col-12">
					<table id="synonym-table" class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>Condition</th>
								<th>Synonym</th>
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
			let synonymTable = $('#synonym-table').DataTable({
				"processing": true,
				"serverSide": true,
				"ajax": "read.php",
				"columns": [
					{ data: "condition_name" },
					{ data: "synonym", className: "editable" }
				],
				"order": [[ 0, 'asc' ]]
			});

			//Enable synonym column as editable
			var simpleEditor = new SimpleTableCellEditor("synonym-table");
            simpleEditor.SetEditableClass("editable");

            $('#synonym-table').on("cell:edited", function (event) {
				let conditionID = event.element.closest("tr").id;
				let newValue = event.newValue;
				$.ajax({
					type: "POST",
					url: "update.php",
					data: {id: conditionID, newVal: newValue}
				});
            });
		}
	</script>
</html>