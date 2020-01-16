<?php
    require_once "db_connect.php";
//    require_once "admin/check_update.php";
    mysqli_close($conn);
?>

<html>
	<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Font -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- JQuery -->
        <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<!-- Bootstrap -->
		<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css"> -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <!-- Datatable -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
        <!-- Chart -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
        <!-- Date Range Picker -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <!-- Tree -->
        <link href="//cdn.syncfusion.com/ej2/ej2-base/styles/material.css" rel="stylesheet">
        <link href="//cdn.syncfusion.com/ej2/ej2-buttons/styles/material.css" rel="stylesheet">
        <link href="//cdn.syncfusion.com/ej2/ej2-navigations/styles/material.css" rel="stylesheet">
        <script src="https://cdn.syncfusion.com/ej2/dist/ej2.min.js" type="text/javascript"></script>

        <!-- Page Js -->
        <script src="index.js"></script>

        <style>
            #chartjs-tooltip {
                opacity: 1;
                position: absolute;
                background: rgba(0, 0, 0, .2);
                color: black;
                border-radius: 3px;
                -webkit-transition: all .1s ease;
                transition: all .1s ease;
                pointer-events: none;
                -webkit-transform: translate(-50%, 0);
                transform: translate(-50%, 0);
            }
            .box {
                padding: 5px;
                margin: 10px 0;
            }
            .box-border {
                border: 1px solid #eee;
            }
            .top-container {
                margin: 0;
                padding: 10px 2%;
            }
            .enable-scroll {
                overflow: auto;
            }
            @media (orientation: landscape) {
                .chart-container {
                    width: 100%;
                    height: 70vh;
                }
            }

            @media (orientation: portrait) {
                .chart-container {
                    width: 100%;
                    height: 60vw;
                }
            }
            .item-box {
                margin: 5px 0;
            }
            .sub-item-box {
                padding-left: 5%;
            }
            .font-bold {
                margin: 0px;
                font-weight: 500;
            }
            .modal-body-content {
				box-shadow: 1px 1px 3px rgba(0,0,0,0.1), -1px -1px 3px rgba(0,0,0,0.1);
                height: calc(60vh);
				padding: 10px;
				overflow: auto;
            }
            .e-treeview > .e-ul {
				overflow: initial !important;
			}
			.graph-search-box {
                box-shadow: 1px 1px 3px rgba(0,0,0,0.1), -1px -1px 3px rgba(0,0,0,0.1);
				padding: 10px;
                display: flex;
                flex-flow: column;
            }
            .height-remaining {
                flex-grow : 1;
                overflow: auto;
            }
        </style>
    </head>
    <body>
        <!-- Main Contents -->
        <div class="top-container"> 
            <div class="row box">
                <div class="col-12">
                    <h1 style="text-align: center"> Studies </h1>
                </div>
            </div>
            <!-- Search -->
            <div class="row box">
                <button class="ml-auto btn btn-primary" data-toggle="modal" data-target="#search-modal"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;Search</button>
            </div>
            <!-- Chart Graph -->
            <div class="row box box-border">
                <div class="col-4 col-md-3 col-lg-2 graph-search-box ">
                    <label class="font-bold">Searched Conditions</label>
                    <div class="form-check-inline">
                        <label class="form-check-label">
                            <input name="search-age-group" type="checkbox" class="form-check-input">Modifier
                        </label>
                    </div>
                    <div class="enable-scroll height-remaining" id="condition-serch-tree">
                    </div>
                </div>
                <div class="col-8 col-md-9 col-lg-10  enable-scroll">
                    <div class="chart-container">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
            <br>
            <!-- Data table -->
            <div class="row box">
                <h4 class="ml-auto mr-auto">Data for graph</h4>
                <br><br>
                <div class="col-12">
                    <table id="study-table" class="table table-striped table-bordered" style="width: 150%">
                        <thead>
                            <tr>
                                <th>NCT ID</th>
                                <th>Title</th>
                                <th>Enrollment</th>
                                <th>Status</th>
                                <th>Study Types</th>
                                <th>Conditions</th>
                                <th>Interventions</th>
                                <th>Outcome Measures</th>
                                <th>Phases</th>
                                <th>Study Designs</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Search Modal -->
        <div class="modal fade" id="search-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Search Studies</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs nav-justified">
                            <li class=" nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-condition">Conditions</a></li>
                            <li class=" nav-item"><a class="nav-link" data-toggle="tab" href="#tab-drug">Drugs</a></li>
                            <li class=" nav-item"><a class="nav-link" data-toggle="tab" href="#tab-other">Others</a></li>
                        </ul>
                        <div class="tab-content" style="margin-top: 10px;">
                            <!-- Condition Tree -->
                            <div class="tab-pane container active" id="tab-condition">
                                <div class="row modal-body-content">
					                <div id="condition-tree">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane container fade" id="tab-drug">
                                <div class="row modal-body-content">
                                    drug
                                </div>
                            </div>
                            <div class="tab-pane container fade" id="tab-other">
                                <form id="search-other-form" style="margin-bottom: 0">
                                    <div class="row modal-body-content">
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-title">Title :</label>
                                            <input id="search-title" name="search-title" class="form-control">
                                        </div>
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-measure">Outcome Measure :</label>
                                            <input id="search-measure" name="search-measure" class="form-control">
                                        </div>
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-design">Design :</label>
                                            <input id="search-design" name="search-design" class="form-control">
                                        </div>
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-type">Type :</label>
                                            <select id="search-type" name="search-type" class="form-control">
                                                <option value="">All</option>
                                                <option>Expanded Access</option>
                                                <option>Interventional</option>
                                                <option>Observational</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-sex">Sex :</label>
                                            <select id="search-sex" name="search-sex" class="form-control">
                                                <option value="">All</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                        <!-- Start Date -->
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-start">Study Start: </label>
                                            <input name="search-start" id="search-start" class="form-control date-range">
                                        </div>
                                        <!-- complete date -->
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-complete">Primary Completion: </label>
                                            <input name="search-complete" id="search-complete" class="form-control date-range">
                                        </div>
                                        <!-- First POsted -->
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-first-post">First Posted: </label>
                                            <input name="search-first-post" id="search-first-post" class="form-control date-range">
                                        </div>
                                        <!-- Last Update -->
                                        <div class="col-12 col-lg-6 item-box">
                                            <label for="search-last-post">Last Update Posted: </label>
                                            <input name="search-last-post" id="search-last-post" class="form-control date-range">
                                        </div>
                                        <!-- Age -->
                                        <div class="col-12 item-box">
                                            <label>Age: </label>
                                            <div class="row sub-item-box">
                                                <div class="col-6">
                                                    <label for="search-age-from"> From </label>
                                                    <input id="search-age-from" name="search-age-from" class="form-control" type="number" min="1">
                                                </div>
                                                <div class="col-6">
                                                    <label for="search-age-from"> To </label>
                                                    <input id="search-age-to" name="search-age-to" class="form-control" type="number" min="1">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Age group -->
                                        <div class="col-12 item-box">
                                            <label>Age Group: </label>
                                            <div class="row sub-item-box">
                                                <div class="form-check-inline">
                                                    <label class="form-check-label">
                                                        <input name="search-age-group" type="checkbox" class="form-check-input" value="Child">Child (birth - 17)
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check-inline">
                                                    <label class="form-check-label">
                                                        <input name="search-age-group" type="checkbox" class="form-check-input" value="Adult">Adult (18 - 64)
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check-inline">
                                                    <label class="form-check-label">
                                                        <input name="search-age-group" type="checkbox" class="form-check-input" value="Older Adult">Older Adult (65+)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Status -->
                                        <div class="col-12 item-box">
                                            <label>Status: </label>
                                            <div class="row sub-item-box">
                                                <div class="col-6">
                                                    <label> Recruitment: </label>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Not yet recruiting">Not yet recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Recruiting">Recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Enrolling by invitation">Enrolling by invitation
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Active, not recruiting">Active, not recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Suspended">Suspended
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Terminated">Terminated
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Completed">Completed
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Withdrawn">Withdrawn
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Unknown status">Unknown status
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label for="search-age-from"> Expanded Access: </label>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Available">Available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="No longer available">No longer available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Temporarily not available">Temporarily not available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Approved for marketing">Approved for marketing
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Phase -->
                                        <div class="col-12 item-box">
                                            <label>Phase: </label>
                                            <div class="row sub-item-box">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Early Phase 1">Early Phase 1
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 1">Phase 1
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 2">Phase 2
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 3">Phase 3
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 4">Phase 4
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Not Applicable">Not Applicable
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="search()"><i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;Search</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>