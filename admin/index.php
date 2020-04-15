<html>
	<head>
        <title> US Clinical Trials Manage</title>
        <link rel="shortcut icon" href="/imgs/clinical_trial_icon.png">

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <style>
            .image {
                width: 100%;
            }
            .split {
                background: #eee;
                height: 1px;
                margin: 20px 0;
            }
            .link {
                color: #3f51b5!important
            }
            .title {
                margin: 30px 0;
                text-align: center;
            }
        </style>

        <script>
            function scrape() {
                if (confirm("Are you sure to scrape data? This may take 4 hours.")) {
                    window.open("scrape_controll.php");
                }
            }
        </script>
	</head>
	
	<body>
		<div class="container">
            <h3 class="title">Scrape Management</h3>
            <div class="row">
                <div class="ml-auto">
                    <a href="/admin_analysis.php" class="btn btn-primary" target="_blank"> Admin Analysis </a>
                    <a href="/admin/feedback.php" class="btn btn-primary" target="_blank" title="Check customers' feedback"> Feedback </a>
                </div>
            </div>

            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
			<div class="row">
                <div class="col-8">
                    <img class="image" src="img/tree.png">
                </div>
                <div class="col-4">
                    <h5> Conditions Hierarchy Management</h5>
                    There are almost 30, 000 Conditions in extracted data.
                    <br>
                    In order to manage trials effectively, you can create conditions hierarchy by clicking bellow button.
                    <br>
                    Here you can manage modifiers too.
                    <br>
                    <div class="row">
                        <a class="ml-auto mr-auto btn btn-primary" href="condition" target="_blank">Disease Modifier & Hierarchy</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
            <div class="row">
                <div class="col-4">
                    <h5> Condition Synonym Management </h5>
                    You can insert synonyms for conditions. For every conditions can be searched by origin name and synonym.
                    <br><br>
                    <div class="row">
                        <a class="ml-auto mr-auto btn btn-primary" href="condition/synonym" target="_blank">Condition Synonym</a>
                    </div>
                </div>
                <div class="col-8">
                    <img class="image" src="img/synonym.png">
                </div>
            </div>
            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
            <div class="row">
                <div class="col-8">
                    <img class="image" src="img/drug.png">
                </div>
                <div class="col-4">
                    <h5> Drug/Treatment Hierarchy Management</h5>
                    There are many drugs in extracted data.
                    <br>
                    In order to manage trials effectively, you can create drug/treatment hierarchy by clicking bellow button.
                    <br><br>
                    <div class="row">
                        <a class="ml-auto mr-auto btn btn-primary" href="drug" target="_blank">Drug/Treatment Hierarchy</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
            <div class="row">
                <div class="col-4">
                    <h5> Drug/Treatment Synonym Management </h5>
                    You can insert synonyms for drug/yreatment. For every drug/treatment can be searched by origin name and synonym.
                    <br><br>
                    <div class="row">
                        <a class="ml-auto mr-auto btn btn-primary" href="drug/synonym" target="_blank">Drug/Treatment Synonym</a>
                    </div>
                </div>
                <div class="col-8">
                    <img class="image" src="img/synonym.png">
                </div>
            </div>
            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
            <div class="row">
                <div class="col-8">
                    <img class="image" src="img/terms.png">
                </div>
                <div class="col-4">
                    <h5> Extract Terms From Scraped Data </h5>
                    As scraped data will change for every scraping, it's needed to extract terms (age groups, conditions - conditions, phases, intervention types, study design types, statuses, study types).
                    The extracted terms will be saved on each table of database. 
                    <br>
                    In order to speed up "Condition" search, it extracts study nct_id and conditions in separated table.
                    <br>
                    It will take a few ten minutes to complete this task.
                    <br><br>
                    <div class="row">
                        <a class="ml-auto mr-auto btn btn-primary" href="terms_control.php" target="_blank">Extract</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-8 ml-auto mr-auto split"></div>
            </div>
			<div class="row">
                <div class="col-4">
                    <h5>Scrape Data</h5>
                    Scrape data from <a href="https://clinicaltrials.gov">https://clinicaltrials.gov</a> into database. In backend, scrape data automatically every 7 days.
                    It take about 4 hours to scrape all data.
                    <br><br>
                    If you want to scrape manually, click below button
                    <br><br><br>
                    <div class="row">
                        <button class="ml-auto mr-auto btn btn-primary" onclick="scrape()">Scrape Manually</button>
                    </div>
                </div>
                <div class="col-8">
                    <img class="image" src="img/scrape.png">
                </div>
            </div>
		</div>
</html>