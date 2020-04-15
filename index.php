<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/check_update.php";
?>

<html>
	<head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-163542949-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-163542949-1', { 'optimize_id': 'GTM-53CCXWH'});
        </script>

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-P45ZHDP');
        </script>
        <!-- End Google Tag Manager -->

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Intelligent navigation of USA clinical trails </title>
        <meta name="description" content="Provide a graphical interface for navigating clinical trials in the USA repository. This website uses a biologist-curated hierarchy of both illness conditions and treatments to provide the user convenient categories.">
        
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
                margin-top: 10px;
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
            .dataTables_length {
                float: left;
            }
            .dt-buttons {
                float: right !important;
            }
        </style>
    </head>
    <body>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P45ZHDP"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

        <div class="lds-container" id="waiting">
			<div class="lds-dual-ring"></div>
		</div>
        <!-- Main Contents -->
        <div class="top-container"> 
            <div class="row box">
                <img src="/imgs/clinical_index.png" style="height: 80px; width: auto" alt="clinical trials">
                <div style="margin-left: 3rem; margin-top: 1rem">
                    <button class="btn btn-outline-danger" style="padding: 0.5rem 1rem" onclick="searchCorona()" 
                        data-intro='Search all trials related to COVID-19' data-step='1'>
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;COVID-19
                    </button>
                    <button class="btn btn-outline-warning" style="padding: 0.5rem 1rem" onclick="searchCancer()"
                        data-intro='Search all trials related to cancer.' data-step='2'>
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;&nbsp;Cancer
                    </button>
                </div>
                <div class="col text-right" style="margin-top: 1rem">
                    <a class="btn btn-outline-success" style="padding: 0.5rem 1rem" href="https://fluidsforlife.com/category/system.html" target="_blank"
                        data-intro='External navigation to high-throughput micro-physiological screening systems, which provide efficient means for evaluating treatments for COVID-19, and other diseases, such as cancer.' data-step='3'>
                        <i class="fa fa-external-link-alt" aria-hidden="true"></i>&nbsp;&nbsp;Micro-physiological systems
                    </a>
                </div>
            </div>
            <!-- Search -->
            <div class="row">
                <div class="col">
                    <h1 class="text-center" id="title_graph" style="font-size: 30px"> Clinical Trials Grouped by Conditions</h1>
                </div>
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
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
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
                            <div id="condition-search-tree"></div>
                        </div>
                        <!-- Modifier Tree -->
                        <div class="tab-pane container graph-left fade" id="graph-tab-modifier">
                            <!-- Condition Tree -->
                            <div id="modifier-tree"></div>
                        </div>
                        <!-- Drug -->
                        <div class="tab-pane container graph-left fade" id="graph-tab-drug">
                            <div id="drug-search-tree"></div>
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
            
            <!-- Data table -->
            <h2 class="text-center" style="margin-top: 2rem; font-size: 30px">Clinical Trials Data Table For Graph</h2>
            <div class="row box">
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
                            <strong>USclinicaltrials.org</strong> provides a <strong>graphical interface</strong> for intelligent navigation of <strong>clinical trials</strong> in the <strong>USA repository</strong>. 
                            FDA regulations require that all trials registered in USA to be published on <a href="https://clinicaltrials.gov">ClinicalTrials.Gov</a> website. 
                            There are over one third of a million trials, and it is very difficult to navigate.
                        </p>
                        <p>
                            USclinicaltrials.org allows the user to graphically select trials by <strong>conditions</strong> and <strong>treatment</strong>.
                            It’s provides <strong>convenient output functions</strong> for selected trials in the dynamic bar graph, as well as <strong>export</strong> in several formats.
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


        <!-- Stylesheets -->
        <!-- Font -->
        <link async rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

		<!-- Bootstrap -->
        <link async rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

        <!-- Datatable -->
		<link async rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
		<link async rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">

        <!-- Date Range Picker -->
        <link async rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <!-- Tree -->
        <link async href="//cdn.syncfusion.com/ej2/ej2-base/styles/material.css" rel="stylesheet">
        <link async href="//cdn.syncfusion.com/ej2/ej2-buttons/styles/material.css" rel="stylesheet">
        <link async href="//cdn.syncfusion.com/ej2/ej2-navigations/styles/material.css" rel="stylesheet">

        <!-- Tour -->
        <link async href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css" rel="stylesheet"/>
        
        <!-- Javascripts -->

        <!-- JQuery -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <!-- Bootstrap -->
        <script async src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script async src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        
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
        <script async src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js"></script>

        <!-- Page Js -->
        <script src="index.js"></script>

    </body>
</html>