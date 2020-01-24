<html>
	<head>
		<script src="../../ej2/ej2.min.js" type="text/javascript"></script>
		<link href="../../ej2/material.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
		<!-- Inline Edit -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jeditable.js/1.7.3/jeditable.min.js"></script>

		<style>
			body{
				touch-action:none;
			}
			.box {
				box-shadow: 1px 1px 3px rgba(0,0,0,0.1), -1px -1px 3px rgba(0,0,0,0.1);
				padding: 5px;
			}
			.big-box {
				overflow: auto;
				height: calc(100vh - 250px);
			}
			.small-box {
				overflow: auto;
				height: calc(100vh - 300px);
			}
			.right-box {
				box-shadow: 1px 1px 3px rgba(0,0,0,0.1), -1px -1px 3px rgba(0,0,0,0.1);
				padding: 5px 20px;;
				height: calc(100vh - 120px);
				overflow: auto;
			}
			.padding-5 {
				padding: 5px;
			}
			.tree-title {
				margin: 5px;
				text-align: center;
			}
			.title {
				margin: 20px;
				text-align: center;
			}
			.tree-box {
				background: rgb(245, 245, 245);
			}
			.e-treeview > .e-ul {
				overflow: initial !important;
			}
			.lds-container {
				width: 100%;
				height: 100%;
				background: rgba(0,0,0,0.6);
				z-index: 1000;
				position: fixed;
				top: 0;
				left: 0;
				display: flex;
			}
			.lds-dual-ring {
				display: inline-block;
				width: 80px;
				height: 80px;
				margin: auto;
			}
			.lds-dual-ring:after {
				content: " ";
				display: block;
				width: 64px;
				height: 64px;
				margin: 8px;
				border-radius: 50%;
				border: 6px solid #fff;
				border-color: #fff transparent #fff transparent;
				animation: lds-dual-ring 1.2s linear infinite;
			}
			@keyframes lds-dual-ring {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}

        </style>
	</head>
	
	<body>
		<div class="lds-container" id="waiting">
			<div class="lds-dual-ring"></div>
		</div>
		<div class="container">
			<h4 class="title">Manage Conditions Hierachy</h4>
			<div class="row">
				<div class="ml-auto padding-5">
					<button class="btn btn-primary" onclick="showCreateCategoryDlg()"><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;Create New Category</button>
					<button title="In order to speed up calculation of study statistics, Calculate studies related with conditions in hierarchy. NOTE: This may take long. Please click after hierarchy looks good." 
						class="btn btn-primary" onclick="calculateStudyCondition()"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;&nbsp;Calculate Study Condition</button>
				</div>
			</div>

			<div class="row">
				<div class="col-6 col-md-5 col-lg-4 box">
					<h5 class="tree-title">All Condtions</h5>
					<div class="row padding-5">
						<div class="col-7 offset-1" style="padding: 0">
							<input class="form-control" placeholder="Search" id="search" onchange="search()"/>
						</div>
						<button class="btn btn-primary" onclick="search()" title="Search"><i class="fa fa-search" aria-hidden="true"></i></button>
						<button class="offset-1 btn btn-danger" onclick="deleteNode()" title="Delete Selected Conditions"><i class="fa fa-trash" aria-hidden="true"></i></button>
					</div>
					<div class="big-box tree-box" id="tree-0"></div>
					<div class="row padding-5" style="margin-top: 5px">
						<button id="prev-page" class="ml-auto btn btn-primary" onclick="prevPage()" title="Prev Page" disabled>
							<i class="fa fa-angle-double-left"></i>
						</button>
						<div class="col-2" style="padding: 0">
							<input type="number" value="1" class="form-control" id="page" editable="false" min="1" readonly>
						</div>
						<button id="next-page" class="mr-auto btn btn-primary" onclick="nextPage()" title="Next Page">
							<i class="fa fa-angle-double-right"></i>
						</button>
					</div>
				</div>
				<div class="col-6 col-md-7 col-lg-8">
					<div class="row right-box" id="managed-trees">
					</div>
				</div>
			</div>
		</div>

<!-- Create Category Dialog -->
		<div class="modal" id="create-category-dlg" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Create New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="category-name">Category Name :</label>
                            <input class="form-control" id="category-name" name="category-name" title="Please insert condition category name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="createCategory()">Create</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
		</div>
	</body>
	<script src="index.js"></script>
</html>