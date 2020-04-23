let studyTable;                     //Data table object.
let conditionTree;                  //Condition Tree in search dialog
let conditionSearchTree;            //Condition tree on the left of graph
let drugTree;                       //Drug tree in search dialog
let drugSearchTree;                 //Drug tree on the left of graph
let modifierTree;                   //Modifier tree on the left of graph
let modifiers = [];                      // modifier array.
let searchItems;                    // all Search items of search dialog
let searchCheckedIds = [];                    // all Search items of search dialog
let loadedCnt = 0;                  // the number of loaded data , 1 - graph data, 2 - table data
let graphSrcData;                   // graph origin data, which is filtered by search.
let graphDrawDetails;               // the sub data of graphSrcData to be displayed on graph
let chartGraph;                     // Graph Object
let isModifier;                     // true: draw modifier on the graph, false: not
let bgColor = [
    "rgba(255, 99, 132, 0.2)",
    "rgba(255, 159, 64, 0.2)",
    "rgba(255, 205, 86, 0.2)",
    "rgba(75, 192, 192, 0.2)",
    "rgba(54, 162, 235, 0.2)",
    "rgba(153, 102, 255, 0.2)",
    "rgba(201, 203, 207, 0.2)"];    // graph bar background color
let bdColor = [
    "rgb(255, 99, 132)",
    "rgb(255, 159, 64)",
    "rgb(255, 205, 86)",
    "rgb(75, 192, 192)",
    "rgb(54, 162, 235)",
    "rgb(153, 102, 255)",
    "rgb(201, 203, 207)"];          // graph bar border color
let conditionCheckedAuto = false;   // when click search button, condition(drug)SearchTree is initialized automatically and check all.
let modifierCheckedAuto = false;    // but this code update graph when node checked change, so it's used to prevent auto load again.
let drugCheckedAuto = false;
let loadedTreeCnt = 0;              // there are 2 main trees. 1 - condition tree, 2 - drug tree. when all trees are loaded, load graph data.
let graphShowKey = "conditions";    // graph showing key. conditions: draw condition as x axis. drugs: draw drug as x axis.

let graphStudyIds = [];             // displayed graph data study ids
let emptyTable = false;
let allData = false;

let otherIds = [];
let observationIds = [];
let isAbnormalData = false;
let isObservation = false;

let isAllSearch = true;
let localGraphData;

initIndexedDB();

$(document).ready(function() {
    ej.base.enableRipple(true);
    initChart();
    //initDatatable();
    initSearchConditionTree();
    initConditionTree();
    initSearchDrugTree();
    initDrugTree();
    initDateRangePicker();
    initModifiers();
    initGraphTab();
    initTour();
} );

function initIndexedDB() {
    window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
    // DON'T use "var indexedDB = ..." if you're not in a function.
    // Moreover, you may need references to some window.IDB* objects:
    window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction || window.msIDBTransaction || {READ_WRITE: "readwrite"}; // This line should only be needed if it is needed to support the object's constants for older browsers
    window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;
    if (!window.indexedDB) {
        console.log("Your browser doesn't support a stable version of IndexedDB. Such and such feature will not be available.");
    }
    var request = window.indexedDB.open("graph_db", 3);
    request.onupgradeneeded = function() {
        let db = request.result;
        if (!db.objectStoreNames.contains('graph_data')) { // if there's no "books" store
          db.createObjectStore('graph_data'); // create it
        }
    };
    request.onsuccess = function() {
        let db = request.result;
        let transaction = db.transaction("graph_data", "readwrite"); // (1)
        let graphDB = transaction.objectStore('graph_data');
        let objectStore = graphDB.get('data');
        objectStore.onsuccess = function() {
            if (objectStore.result) {
                localGraphData = JSON.parse(objectStore.result);
            }
        }
    };
}

function initTour() {
    $('#start_tour').click(function(){
		introJs().start();
	});
}
function initGraphTab() {
    $('#graph-tab a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $("#title_graph").html("Clinical Trials Grouped by " + $(this).html());
        chartGraph.options.scales.xAxes[0].scaleLabel.labelString = $(this).html();
        updateGraph(false);
    });
}

function resetZoom() {
    // chartGraph.resetZoom();
}

function initDateRangePicker() {
    $('.date-range').daterangepicker();
    $('.date-range').val('');
    $('.date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
}

function initDatatable() {
    studyTable = $('#study-table').DataTable({
        bFilter: false,
        searching: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        scrollY: "70vh",
        ajax: {
            type: "POST",
            url: "read_table_data.php",
            data: function ( d ) {
                let searchKeys = {};
                if (emptyTable) {
                    searchKeys.emptyTable = true;    
                } else {
                    if (isAbnormalData) {
                        if (isObservation) {
                            searchKeys.manual_ids = JSON.stringify(observationIds);
                        } else {
                            searchKeys.manual_ids = JSON.stringify(otherIds);
                        }
                    } else {
                        searchKeys.manual_ids = JSON.stringify(graphStudyIds);
                    }
                }
                return  $.extend(d, searchKeys);
            },
        },
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: [
                    {extend: 'excel',title: "studies"},
                    {extend: 'csv',title: "studies"},
                    {extend: 'print'},
                ]
            }
        ],
        columns: [
            //{ data: "rank" },
            { data: "nct_id" },
            { data: "title" },
            { data: "enrollment" },
            { data: "status" },
            { data: "study_types" },
            { data: "conditions" },
            { data: "interventions" },
            { data: "outcome_measures" },
            { data: "phases" },
            { data: "study_designs" },
        ],
        order: [[ 0, 'desc' ]]
    });
    $('button.dt-button').attr("data-intro", "Export data into Excel and CSV");
    $('button.dt-button').attr("data-step", "13");
}

function initChart() {
        var data = {
            datasets: [{
                //data: [10, 20, 30],
                borderWidth: 1
            }]
        };
          
        var options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }, 
                    scaleLabel: {
                        display: true,
                        labelString: 'The number of trials',
                        fontSize: 16,
                        fontStyle: 'bold',
                        padding: 10,
                        fontColor: '#007bff'
                    }
                }],
                xAxes: [
                    {
                        scaleLabel: {
                            display: true,
                            labelString: 'Conditions',
                            fontSize: 16,
                            fontStyle: 'bold',
                            padding: 10,
                            fontColor: '#007bff'
                        }
                    }
                ]
            },
            responsiveAnimationDuration: 200,
            plugins: {
                zoom: {
                    pan: {
                        enabled: true,
                        mode: 'x',
                    },
                    zoom: {
                        enabled: true,
                        drag: false,
                        mode: 'x',
                        limits: {
                            max: 10,
                            min: 0.5
                        }
                    }
                }
            },
            onclick: barClicked
        };

        let canv = document.getElementById("myChart");
        canv.addEventListener('click', barClicked, false);
        let ctx = canv.getContext("2d");
        chartGraph = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
}

function barClicked(e) {
    let clickedElement = chartGraph.getElementAtEvent(e);
    if (!clickedElement || clickedElement.length < 1 || clickedElement[0]._index < -1) {
        return;
    }

    let clickedBarDetail = graphDrawDetails[clickedElement[0]._index];
    if(!clickedBarDetail) {
        return;
    }
    if (clickedBarDetail.node == "other") {
        isAbnormalData = true;
        isObservation = false;
        if (clickedBarDetail.nodeType == "observation") {
            isObservation = true;
        }
    } else {
        conditionCheckedAuto = true;
        modifierCheckedAuto = true;
        drugCheckedAuto = true;
    
        if (graphShowKey == "conditions") {
            conditionSearchTree.uncheckAll();
            conditionSearchTree.checkedNodes = [clickedBarDetail.node.nodeId];
            conditionSearchTree.refresh();
            if(clickedBarDetail.modifier) {
                modifierTree.uncheckAll();
                modifierTree.checkedNodes = [clickedBarDetail.modifier.nodeId];
                modifierTree.refresh();
                modifierTree.expandAll();
                $('.nav-tabs a[href="#graph-tab-modifier"]').tab('show');
            }
        } else {
            drugSearchTree.uncheckAll();
            drugSearchTree.checkedNodes = [clickedBarDetail.node.nodeId];
            drugSearchTree.refresh();
        }
    
        conditionCheckedAuto = false;
        modifierCheckedAuto = false;
        drugCheckedAuto = false;
    }
    
    updateGraph();

    isAbnormalData = false;
    isObservation = false;
}

function initConditionTree() {
    $.ajax({
        url: "read_condition_tree.php",
        success: function(response) {
            if (response) {
                try {
                    data = JSON.parse(response);
                    conditionTree.fields.dataSource = data;
                    conditionTree.refresh();
                    conditionTree.checkAll();
                    conditionTree.expandAll();

                    conditionSearchTree.fields.dataSource = data;
                    conditionSearchTree.refresh();
                    conditionSearchTree.expandAll();

                    loadedTreeCnt++;
                    readGraphData();
                } catch (e) {
                    console.log(e);
                }
            }
        }
    });

    conditionTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true
    });
    conditionTree.appendTo("#condition-tree");
}

function initSearchConditionTree() {
    conditionSearchTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true,
        nodeChecked: function() {
            if (!conditionCheckedAuto) {
                updateGraph();
            }
        }
    });
    conditionSearchTree.appendTo("#condition-search-tree");
}

function initDrugTree() {
    $.ajax({
        url: "read_drug_tree.php",
        success: function(response) {
            if (response) {
                try {
                    data = JSON.parse(response);
                    drugTree.fields.dataSource = data;
                    drugTree.refresh();
                    drugTree.checkAll();
                    drugTree.expandAll();
                    
                    drugSearchTree.fields.dataSource = data;
                    drugSearchTree.refresh();
                    drugSearchTree.expandAll();

                    loadedTreeCnt++;
                    readGraphData();
                } catch (e) {
                    console.log(e);
                }
            }
        }
    });

    drugTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true
    });
    drugTree.appendTo("#drug-tree");
}

function initSearchDrugTree() {
    drugSearchTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true,
        nodeChecked: function() {
            if (!drugCheckedAuto) {
                updateGraph();
            }
        }
    });
    drugSearchTree.appendTo("#drug-search-tree");
}

function readGraphData() {
    // if condition & drug tree nodes are not loaded, don't search.
    if (loadedTreeCnt < 2) {
        return;
    }
    if (!searchItems) {
        readSearchItems();
    }
    // Load search tree from drug tree
    drugCheckedAuto = true;
    drugSearchTree.uncheckAll();
    drugSearchTree.checkedNodes = searchCheckedIds["drugs"];
    drugSearchTree.refresh();
    drugSearchTree.expandAll();
    drugCheckedAuto = false;
    

    // Load search tree from condition tree
    conditionCheckedAuto = true;
    conditionSearchTree.uncheckAll();
    conditionSearchTree.checkedNodes = searchCheckedIds["conditions"];
    conditionSearchTree.refresh();
    conditionSearchTree.expandAll();
    conditionCheckedAuto = false;

    if (!isAllSearch) {
        loadGraphData();
    } else {
        // if there isn't loaded graph data, load data.
        if (!localGraphData) {
            loadGraphData();
        } else {
            $.ajax({
                type: "POST",
                url: "check_latest_graph.php",
                data: {date: localGraphData.date},
                success: function(response) {
                    // if it's latest version, don't load data
                    if (response == "latest") {
                        hideWaiting();
                        graphSrcData = localGraphData;
                        updateGraph();
                        triggerTourEvent();
                    } else {
                        loadGraphData();
                    }
                }
            });
        }
    }
}

function loadGraphData() {
    $.ajax({
        type: "POST",
        url: "read_graph_data.php",
        data: {data: JSON.stringify(searchItems)},
        success: function(response) {
            hideWaiting();
            try {
                graphSrcData = JSON.parse(response);
                if (isAllSearch) {
                    saveAllDataOnLocalDB();
                }
                updateGraph();
                triggerTourEvent();
            } catch(e) {
                console.log(e);
            }
        }
    });
}

function saveAllDataOnLocalDB() {
    let request = window.indexedDB.open("graph_db", 3);
    request.onsuccess = function() {
        let db = request.result;
        let transaction = db.transaction("graph_data", "readwrite"); // (1)
        let graphDB = transaction.objectStore('graph_data');
        graphDB.put(JSON.stringify(graphSrcData), 'data');
    };
}

function triggerTourEvent() {
    if($('#start_tour').length > 0 && localStorage.getItem("clincaltrials_app_tour_shown") !== 'true'){
    	$('#start_tour').trigger('click');
    	localStorage.setItem("clincaltrials_app_tour_shown", 'true');
    }
}

function search() {
    showWaiting();
    // Get search items
    readSearchItems();
    // Load Graph Data
    readGraphData();
    $("#search-modal").modal("hide");
}

function readSearchItems() {
    searchItems = getFormData($("#search-other-form"));
    searchItems["conditions"] = conditionTree.fields.dataSource;
    searchItems["drugs"] = drugTree.fields.dataSource;

    let checkedConditionNodes = getCheckedTreeNodes("condition-tree", conditionTree);
    searchCheckedIds["conditions"] = [];
    checkedConditionNodes.forEach(function(node) {
        searchCheckedIds["conditions"].push(node.nodeId);
    });

    let checkedDrugNodes = getCheckedTreeNodes("drug-tree", drugTree);
    searchCheckedIds["drugs"] = [];
    checkedDrugNodes.forEach(function(node) {
        searchCheckedIds["drugs"].push(node.nodeId);
    });
}

function getFormData(form){
    var unindexed_array = form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i){
        if (!n['value']) {
            return;
        }
        if (indexed_array[n['name']]) {
            if (!indexed_array[n['name']].push) {
                indexed_array[n['name']] = [indexed_array[n['name']]];
            }
            indexed_array[n['name']].push(n['value']);
        } else {
            indexed_array[n['name']] = n['value'];
        }
    });

    // if all checked, ignore
    if (indexed_array["search-status"].length == 13) {
        delete indexed_array["search-status"];
    }
    if (indexed_array["search-phase"].length == 6) {
        delete indexed_array["search-phase"];
    }
    isAllSearch = Object.keys(indexed_array).length < 1;
    return indexed_array;
}

function getCheckedTreeNodes(selector, tree) {
    let checkedNodes = getCheckedNodes(selector);
    
    // console.log("function:", checkedNodes);
    checkedNodes.forEach(element => {
        let nodeObject = tree.getNodeObject(element);
        removeChildren(nodeObject.nodeChild, checkedNodes);
    });
    
    checkedNodes.forEach((element, index) => {
        checkedNodes[index] = tree.getNodeObject(element);
    });
    
    return checkedNodes;
}

function removeChildren(children, checkedNodes) {
    if (!children || children.length < 1 || !checkedNodes || checkedNodes.length < 1) {
        return;
    }
    children.forEach(element => {
        let idx = checkedNodes.indexOf(element.nodeId);
        if ( idx == -1) {
            return;
        }
        checkedNodes.splice(idx, 1);
        removeChildren(element.nodeChild, checkedNodes);
    });
}

function getCheckedNodes(id) {
    let checkedElements = $("#" + id + " .e-check").toArray();
    let checkedNodes = [];
    checkedElements.forEach(element => {
        checkedNodes.push( $(element.closest("li")).data("uid") );
    });
    return checkedNodes;
}

function hideWaiting() {
    $("#waiting").hide();
}

function showWaiting() {
    loadedCnt=0;
    $("#waiting").show();
}

function updateGraph(loadTable = true) {
    let activeTabId = $("#graph-tab .active").attr("href");
    let checkedNodes;

    if (activeTabId == "#graph-tab-drug") {
        isModifier = false;
        checkedNodes = getCheckedTreeNodes("drug-search-tree", drugSearchTree);
        graphShowKey = "drugs";
    } else {
        graphShowKey = "conditions";
        if(activeTabId == "#graph-tab-modifier") {
            isModifier = true;
        } else {
            isModifier = false;
        }
        checkedNodes = getCheckedTreeNodes("condition-search-tree", conditionSearchTree);
    }
    
    let checkedModifierNodes;
    if (isModifier) {
        checkedModifierNodes = getCheckedTreeNodes("modifier-tree", modifierTree);
    }

    if (checkedModifierNodes && checkedModifierNodes.length > 0 && checkedModifierNodes[0].nodeId == "ROOT") {
        checkedModifierNodes = checkedModifierNodes[0].nodeChild;
    }
    // if checked only one category and has children, display the children
    while (checkedNodes.length == 1 && checkedNodes[0].nodeChild.length > 0) {
        checkedNodes = checkedNodes[0].nodeChild;
    }

    if (!isAbnormalData) {
        getPossibleStudyIds(graphShowKey);
    }

    // Update datatable
    if (loadTable || isAbnormalData) {
        updateDatatable();
    }
    
    drawGraph(checkedNodes, checkedModifierNodes);
}

function getPossibleStudyIds(graphShowKey) {
    let conditionNodes = getCheckedTreeNodes("condition-search-tree", conditionSearchTree);
    let modifierNodes = getCheckedTreeNodes("modifier-tree", modifierTree);
    let drugNodes =getCheckedTreeNodes("drug-search-tree", drugSearchTree);
    
    graphStudyIds = [];
    emptyTable = true;
    allData = false;
    otherIds = [];
    observationIds = [];

    if(conditionNodes.length < 1 || modifierNodes.length < 1 || drugNodes.length < 1) {
        return;
    }

    let isAllCondition = conditionNodes[0].nodeId == "ROOT";
    let isAllModifier = modifierNodes[0].nodeId == "ROOT";
    let isAllDrug = drugNodes[0].nodeId == "ROOT";

    //If all is checked, don't merge.
    if (isAllCondition && isAllModifier && isAllDrug) {
        emptyTable = false;
        allData = true;
        return;
    }

    // merge condition and modifier study ids
    let conditionIds = [];
    if (!isAllCondition || !isAllModifier) {
        if (isAllCondition) {
            conditionNodes = conditionNodes[0].nodeChild;
        }
        conditionNodes.forEach(function(conditionNode) {
            let id = conditionNode.nodeId.substr(10);
            if (isAllModifier) {
                conditionIds = conditionIds.concat(graphSrcData["conditions"][id]["studyIds"]);
            } else {
                modifierNodes.forEach(function(modifierNode) {
                    conditionIds = conditionIds.concat(graphSrcData["conditions"][id]['modifier'][modifierNode.nodeText]["studyIds"]);
                });
            }
            conditionIds = conditionIds.filter((item, idx) => conditionIds.indexOf(item) == idx);
        });
    }

    // merge drug study ids
    let drugIds = [];
    if (!isAllDrug) {
        drugNodes.forEach(function(drugNode) {
            let id = drugNode.nodeId.substr(10);
            drugIds = drugIds.concat(graphSrcData["drugs"][id]["studyIds"]);
            drugIds = drugIds.filter((item, idx) => drugIds.indexOf(item) == idx);
        });
    }

    //intersection of condition and drugs
    if (isAllCondition && isAllModifier) {
        graphStudyIds = drugIds;
    } else if (isAllDrug) {
        graphStudyIds = conditionIds;
    } else {
        graphStudyIds = conditionIds.filter( item => drugIds.indexOf(item) != -1 );
    }

    let isConditionActive = $("#graph-tab .active").attr("href") == "#graph-tab-condition";
    if (graphShowKey == "conditions") {
        if (isAllModifier && !isConditionActive) {
            modifierNodes = modifierNodes[0].nodeChild;
            let modifierIds = [];
            conditionNodes.forEach(function(conditionNode) {
                let id = conditionNode.nodeId.substr(10);
                modifierNodes.forEach(function(modifierNode) {
                    modifierIds = modifierIds.concat(graphSrcData["conditions"][id]['modifier'][modifierNode.nodeText]["studyIds"]);
                });
            });
            otherIds = graphStudyIds.filter(item => modifierIds.indexOf(item) == -1);
        }
    } else if (isAllDrug) {
        let drugIds = [];
        drugNodes = drugNodes[0].nodeChild;
        drugNodes.forEach(function(drugNode) {
            let id = drugNode.nodeId.substr(10);
            drugIds = drugIds.concat(graphSrcData["drugs"][id]["studyIds"]);
            drugIds = drugIds.filter((item, idx) => drugIds.indexOf(item) == idx);
        });
        // Get all other ids not in treatment hiearhcy
        otherIds = graphStudyIds.filter(item => drugIds.indexOf(item) == -1);

        // get observation ids from otherids & observation ids.
        observationIds = otherIds.filter(item => graphSrcData["observationalIds"].indexOf(item) != -1);

        // get remained other ids.
        otherIds = otherIds.filter(item => observationIds.indexOf(item) == -1);
    }
 
    emptyTable = graphStudyIds.length < 1;
}


function updateDatatable() {
    if(studyTable) {
        studyTable.ajax.reload();
    } else {
        initDatatable();
    }
}

function drawGraph(nodes, modifierNodes) {
    let graphLabels = [];
    let graphDrawData = [];
    let backgroundColors = [];
    let borderColors = [];
    let chartCnt = 0;
    
    
    
    if (isAbnormalData) {
        if (isObservation) {
            graphDrawDetails = [{node: "other", nodeType: "observation", cnt: observationIds.length}];
            graphLabels = ["Observational"];
            graphDrawData = [observationIds.length];
        } else {
            graphDrawDetails = [{node: "other", nodeType: "other", cnt: otherIds.length}];
            graphLabels = ["Other"];
            graphDrawData = [otherIds.length];
        }
        borderColors = [bdColor[0]];
        backgroundColors = [bdColor[0]];
    } else {
        graphDrawDetails = [];
        nodes.forEach(node => {
            let id = node.nodeId.substr(10);
            // if modifier, extract all data for modifiers.
            if (isModifier) {
                modifierNodes.forEach( modifier=> {
                    let modifierName = modifier.nodeText;
                    let nCnt = 0;
                    if (allData) {
                        nCnt = graphSrcData[graphShowKey][id]["modifier"][modifierName]["studyIds"].length;
                    } else {
                        nCnt = graphSrcData[graphShowKey][id]["modifier"][modifierName]["studyIds"].filter( item => graphStudyIds.indexOf(item) != -1 ).length;
                    }
                    if (nCnt > 0) {
                        graphLabels.push(modifierName + " - " + node.nodeText);
                        graphDrawData.push(nCnt);
                        graphDrawDetails.push({node: node, modifier: modifier, cnt: nCnt});
                        backgroundColors.push(bgColor[chartCnt % 7]);
                        borderColors.push(bdColor[chartCnt % 7]);
                        chartCnt++;
                    }
                });
            } else {
                let nCnt = 0;
                // show all child node data
                if (allData) {
                    nCnt = graphSrcData[graphShowKey][id]["studyIds"].length;
                } else {
                    nCnt = graphSrcData[graphShowKey][id]["studyIds"].filter( item => graphStudyIds.indexOf(item) != -1 ).length;
                }
                // if (nCnt > 0) {
                graphLabels.push(node.nodeText);
                graphDrawData.push(nCnt);
                graphDrawDetails.push({node: node, cnt: nCnt});
                backgroundColors.push(bgColor[chartCnt % 7]);
                borderColors.push(bdColor[chartCnt % 7]);
                chartCnt++;
            }
        });

        if (observationIds.length > 0) {
            graphLabels.push("Observational");
            graphDrawData.push(observationIds.length);
            graphDrawDetails.push({node: "other", nodeType: "observation", cnt: observationIds.length});
            backgroundColors.push(bgColor[chartCnt % 7]);
            borderColors.push(bdColor[chartCnt % 7]);
            chartCnt++;
        }
        if (otherIds.length > 0) {
            graphLabels.push("Other");
            graphDrawData.push(otherIds.length);
            graphDrawDetails.push({node: "other", nodeType: "other", cnt: otherIds.length});
            backgroundColors.push(bgColor[chartCnt % 7]);
            borderColors.push(bdColor[chartCnt % 7]);
            chartCnt++;
        }
    }
    chartGraph.data.labels = graphLabels;
    chartGraph.data.datasets[0].data = graphDrawData;
    chartGraph.data.datasets[0].backgroundColor = backgroundColors;
    chartGraph.data.datasets[0].borderColor = borderColors;
    chartGraph.update();
}
// Read modifiers
function initModifiers() {
    $.ajax({
        url: "read_modifier.php",
        success: function(response) {
            if (response) {
                try {
                    modifiers = JSON.parse(response);
                    let modifierTreeData = [{
                        nodeId: "ROOT",
                        nodeText: "All",
                        nodeChild: []
                    }];
                    let id = 0;
                    modifiers.forEach(modifier => {
                        modifierTreeData[0].nodeChild.push({nodeId: "MODIFIERS-" + id, nodeText: modifier["modifier"]});
                        id++;
                    });
                    modifierCheckedAuto = true;
                    modifierTree.fields.dataSource = modifierTreeData;
                    modifierTree.refresh();
                    modifierTree.checkAll();
                    modifierTree.expandAll();
                    modifierCheckedAuto = false;
                } catch (e) {
                    console.log(e);
                }
            }
        }
    });
    modifierTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true,
        nodeChecked: function() {
            if (!modifierCheckedAuto) {
                updateGraph();
            }
        },
    });
    modifierTree.appendTo("#modifier-tree");
}

function searchCorona() {
    conditionCheckedAuto = true;
    modifierCheckedAuto = true;
    drugCheckedAuto = true;

    conditionSearchTree.uncheckAll();
    conditionSearchTree.checkedNodes = ["CONDITION-141"];
    conditionSearchTree.refresh();

    modifierTree.checkAll();
    modifierTree.refresh();
    modifierTree.expandAll();

    drugSearchTree.checkAll();
    drugSearchTree.refresh();
    drugSearchTree.expandAll();

    conditionCheckedAuto = false;
    modifierCheckedAuto = false;
    drugCheckedAuto = false;

    $('.nav-tabs a[href="#graph-tab-drug"]').tab('show');
    updateGraph();
}

function searchCancer() {
    conditionCheckedAuto = true;
    modifierCheckedAuto = true;
    drugCheckedAuto = true;

    conditionSearchTree.uncheckAll();
    conditionSearchTree.checkedNodes = ["CONDITION-44", "CONDITION-81"];
    conditionSearchTree.refresh();

    modifierTree.checkAll();
    modifierTree.refresh();
    modifierTree.expandAll();

    drugSearchTree.checkAll();
    drugSearchTree.refresh();
    drugSearchTree.expandAll();

    conditionCheckedAuto = false;
    modifierCheckedAuto = false;
    drugCheckedAuto = false;

    updateGraph();
}

function submitFeedback() {
    if (!$("#feedback").val()) {
        alert("Please add feedback contents.");
    }
    $.ajax({
        type: "POST",
        url: "/feedback/add.php",
        data: {content: $("#feedback").val()},
        success: function(response) {
            alert("Thank you for your feedback!");
        }
    });
}