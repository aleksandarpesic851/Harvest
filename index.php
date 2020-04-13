<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/check_update.php";
?>

<html>
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Intelligent navigation of USA clinical trails </title>
        <meta name="description" content="Provide a graphical interface for navigating clinical trials in the USA repository. Allows the user to graphically select trials by conditions and treatment. This website uses a biologist-curated a hierarchy of both illness conditions and treatments to provide the user convenient categories. This allows comparison of related conditions or treatments.">
        
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ClinicalTrails",
            "name": "Intelligent navigation of USA clinical trails",
            "url": "http://usclinicaltrials.org",
            "description": "USclinicaltrials.org provide a graphical interface for navigating clinical trials in the USA repository. FDA regulations require that all trials registered in USA to be published on ClinicalTrials.Gov website. There are over one third of a million trials, and it is very difficult to navigate. USclinicaltrials.org allows the user to graphically select trials by conditions and treatment. It’s provides convenient output functions for selected trials in the dynamic bar graph, as well as export in several formats.This website uses a biologist-curated a hierarchy of both illness conditions and treatments to provide the user convenient categories. This allows comparison of related conditions or treatments. For example, for a given disease-treatment combination a patient might desire to see alternative treatments that fit his disease, while a company might want to see alternative diseases, which might respond to its treatment.",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+1-617-775-9778",
                "email": "info@flowcell.co",
                "address": "29 Littles Point Rd. Swampscott, MA 01907, USA",
                "contactType": "Customer service"
            }
        }
        </script>

        <link rel="shortcut icon" href="/imgs/clinical_trial_icon.png">
        <!-- Font -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

		<!-- Bootstrap -->
		<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css"> -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

        <!-- Datatable -->
        <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.css">
        <script type="text/javascript" src="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.js"></script> -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">

        <!-- Date Range Picker -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <!-- Tree -->
        <link href="//cdn.syncfusion.com/ej2/ej2-base/styles/material.css" rel="stylesheet">
        <link href="//cdn.syncfusion.com/ej2/ej2-buttons/styles/material.css" rel="stylesheet">
        <link href="//cdn.syncfusion.com/ej2/ej2-navigations/styles/material.css" rel="stylesheet">

        <!-- Tour -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css" rel="stylesheet"/>
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
                .graph-left {
                    max-height: calc(70vh - 50px);
                    overflow: auto;
                }
            }

            @media (orientation: portrait) {
                .chart-container {
                    width: 100%;
                    height: 80vw;
                }
                .graph-left {
                    max-height: calc(60vw - 50px);
                    overflow: auto;
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
            canvas {
                -moz-user-select: none;
                -webkit-user-select: none;
                -ms-user-select: none;
            }
            .font-12 {
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class="lds-container" id="waiting">
			<div class="lds-dual-ring"></div>
		</div>
        <!-- Main Contents -->
        <div class="top-container"> 
            <div class="row box">
                <img src="/imgs/clinical_index.png" style="height: auto; max-width: 80%; width: 400px" alt="clinical trials">
                <div style="margin-left: 3rem; margin-top: 2rem">
                    <button class="btn btn-outline-danger" style="padding: 1rem 2rem" onclick="searchCorona()" 
                        data-intro='Search all trials relate to COVID-19' data-step='1'>
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;COVID-19
                    </button>
                    <button class="btn btn-outline-warning" style="padding: 1rem 2rem" onclick="searchCancer()"
                        data-intro='Search all trials relate to cancer.' data-step='2'>
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;Cancer
                    </button>
                </div>
                <div class="col text-right" style="margin-top: 2rem">
                    <a class="btn btn-outline-success" style="padding: 1rem 2rem" href="https://fluidsforlife.com/" target="_blank"
                        data-intro='External navigation to high-throughput micro-physiological screening systems, which provide efficient means for evaluating treatments for COVID-19, and other diseases, such as cancer.' data-step='3'>
                        <i class="fa fa-external-link-alt" aria-hidden="true"></i>&nbsp;&nbsp;Micro-physiological systems
                    </a>
                </div>
            </div>
            <h1 class="text-center" id="title_graph"> Clinical Trials By Conditions</h1>
            <!-- Search -->
            <div class="row box">
                <div class="ml-auto">
                    <button id="btn-zoom-in" class="btn btn-success" title="Reset Zoom & Pan" onclick="resetZoom()"
                        data-intro='Reset zoom of graph.' data-step='4'>
                        <i class="fa fa-refresh"></i>&nbsp;&nbsp; Reset Zoom
                    </button>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#search-modal"
                        data-intro='Search US clinical trials database. Search can include Condition (disease), Treatment (intervention) and Additional options, such as trials status can be set under "Other".' data-step='5'>
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;Search
                    </button>
                    <button type="button" id="start_tour" title="Tour Website" data-toggle="tooltip" data-placement="bottom"
                        class="btn btn-info btn-flat hidden-xs" style="padding: 10px 20px"
                        data-intro='Tour Website.' data-step='6'>
                        <strong><i class="fa fa-question-circle fa-lg" aria-hidden="true"></i></strong>
                    </button>
                    <button class="btn btn-info" data-toggle="modal" data-target="#about-modal"
                        data-intro='Description about this website' data-step='7'>
                        About Us
                    </button>
                </div>
            </div>
            <!-- Chart Graph -->
            <div class="row box box-border">
                <div class="col-12 col-lg-4 col-xl-3">
                    <ul class="nav nav-tabs nav-justified" id="graph-tab">
                        <li class=" nav-item" data-intro='Filter search results by condition only.' data-step='8'><a class="nav-link font-12 active" data-toggle="tab" href="#graph-tab-condition">Conditions</a></li>
                        <li class=" nav-item" data-intro='Filter search results by modifier only.' data-step='9'><a class="nav-link font-12" data-toggle="tab" href="#graph-tab-modifier">Modifiers</a></li>
                        <li class=" nav-item" data-intro='Filter search results by treatment only.' data-step='10'><a class="nav-link font-12" data-toggle="tab" href="#graph-tab-drug">Treatments</a></li>
                    </ul>
                    <div class="tab-content" style="margin-top: 10px;">
                        <div class="tab-pane container graph-left active" id="graph-tab-condition">
                            <!-- Condition Tree -->
                            <div id="condition-search-tree">
                            </div>
                        </div>
                        <!-- Modifier Tree -->
                        <div class="tab-pane container graph-left fade" id="graph-tab-modifier">
                            <!-- Condition Tree -->
                            <div id="modifier-tree">
                            </div>
                        </div>
                        <!-- Drug -->
                        <div class="tab-pane container graph-left fade" id="graph-tab-drug">
                            <div id="drug-search-tree">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Chart Graph -->
                <div class="col-12 col-lg-8 col-xl-9 enable-scroll">
                    <div class="chart-container" 
                        data-intro="Graph for filtered data. Zoom in and out of this graph using mouse wheel scroll." data-step='11'>
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
            <br>
            <!-- Data table -->
            <h2 class="text-center">Clinical Trials Data Table For Graph</h2>
            <div class="row box">
                <br><br>
                <div class="col-12" data-intro='Data table for filtered data' data-step='12'>
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
                        <h2 class="modal-title" id="exampleModalLongTitle">Search Studies</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs nav-justified">
                            <li class=" nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-condition">Conditions</a></li>
                            <li class=" nav-item"><a class="nav-link" data-toggle="tab" href="#tab-drug">Treatments</a></li>
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
                                    <div id="drug-tree">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane container fade" id="tab-other">
                                <form id="search-other-form" style="margin-bottom: 0">
                                    <div class="row modal-body-content">
                                        <!-- Status -->
                                        <div class="col-12 item-box">
                                            <label>Status: </label>
                                            <div class="row sub-item-box">
                                                <div class="col-6">
                                                    <label> Recruitment: </label>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Not yet recruiting" checked>Not yet recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Recruiting" checked>Recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Enrolling by invitation" checked>Enrolling by invitation
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Active, not recruiting" checked>Active, not recruiting
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Suspended" checked>Suspended
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Terminated" checked>Terminated
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Completed" checked>Completed
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Withdrawn" checked>Withdrawn
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Unknown status" checked>Unknown status
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label for="search-age-from"> Expanded Access: </label>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Available" checked>Available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="No longer available" checked>No longer available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Temporarily not available" checked>Temporarily not available
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input name="search-status" type="checkbox" class="form-check-input" value="Approved for marketing" checked>Approved for marketing
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
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Early Phase 1" checked>Early Phase 1
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 1" checked>Phase 1
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 2" checked>Phase 2
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 3" checked>Phase 3
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Phase 4" checked>Phase 4
                                                    </label>
                                                </div>&nbsp;&nbsp;&nbsp;
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input name="search-phase" type="checkbox" class="form-check-input" value="Not Applicable" checked>Not Applicable
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
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

        <!-- About Modal -->
        <div class="modal fade" id="about-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">About us</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>
                            <strong>USclinicaltrials.org</strong> provide a <strong>graphical interface</strong> for navigating <strong>clinical trials</strong> in the <strong>USA repository</strong>. 
                            FDA regulations require that all trials registered in USA to be published on <a href="https://clinicaltrials.gov">ClinicalTrials.Gov</a> website. 
                            There are over one third of a million trials, and it is very difficult to navigate.
                        </p>
                        <p>
                            <strong>USclinicaltrials.org</strong> allows the user to graphically select trials by <strong>conditions</strong> and <strong>treatment</strong>.
                            It’s provides <strong>convenient output functions for selected trials in the dynamic bar graph</strong>, as well as <strong>export</strong> in several formats.
                        </p>
                        <p>
                            This website uses a <strong>biologist-curated hierarchy of both illness conditions and treatments</strong> to provide the user convenient categories. 
                            This allows <strong>comparison of related conditions or treatments</strong>. 
                            For example, for a given disease-treatment combination a patient might desire to see alternative treatments that fit his disease, while a company might want to see alternative diseases, which might respond to its treatment.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Javascripts -->

        <!-- JQuery -->
        <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.3.1.js"></script>
        
        <!-- Bootstrap -->
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        
        <!-- Datatable -->
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
        
        <!-- Chart -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3"></script>
        <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@0.7.4"></script>
        
        <!-- Date RangePicker -->
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <!-- Treeview -->
        <script src="https://cdn.syncfusion.com/ej2/dist/ej2.min.js" type="text/javascript"></script>

        <!-- Tour -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js"></script>

        <!-- Page Js -->
        <script src="index.js"></script>

    </body>
</html>