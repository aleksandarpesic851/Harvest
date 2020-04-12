let studyTable;                     //Data table object.
let conditionTree;                  //Condition Tree in search dialog
let conditionSearchTree;            //Condition tree on the left of graph
let drugTree;                       //Drug tree in search dialog
let drugSearchTree;                 //Drug tree on the left of graph
let modifierTree;                   //Modifier tree on the left of graph
let modifiers = [];                      // modifier array.
let searchItems;                    // all Search items of search dialog
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

function initTour() {
    $('#start_tour').click(function(){
		introJs().start();
	});
}
function initGraphTab() {
    $('#graph-tab a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $("#title_graph").html("Clinical Trials By " + $(this).html());
        updateGraph();
    });
}

function resetZoom() {
    chartGraph.resetZoom();
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
                    searchKeys.manual_ids = JSON.stringify(graphStudyIds);
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
        drawCallback: function() {
            hideWaiting();
        },
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
                    }
                }]
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
        } else {
            modifierTree.checkAll();
        }
    } else {
        drugSearchTree.uncheckAll();
        drugSearchTree.checkedNodes = [clickedBarDetail.node.nodeId];
        drugSearchTree.refresh();
    }

    conditionCheckedAuto = false;
    modifierCheckedAuto = false;
    drugCheckedAuto = false;
    
    updateGraph();
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
    drugSearchTree.fields.dataSource = searchItems["drugs"];
    drugSearchTree.refresh();
    drugSearchTree.checkAll();
    drugSearchTree.expandAll();
    drugCheckedAuto = false;
    

    // Load search tree from condition tree
    conditionCheckedAuto = true;
    conditionSearchTree.fields.dataSource = searchItems["conditions"];
    conditionSearchTree.refresh();
    conditionSearchTree.checkAll();
    conditionSearchTree.expandAll();
    conditionCheckedAuto = false;

    //load graph data
    $.ajax({
        type: "POST",
        url: "read_graph_data.php",
        data: searchItems,
        success: function(response) {
            hideWaiting();
            try {
                graphSrcData = JSON.parse(response);
                updateGraph();
                triggerTourEvent();
            } catch(e) {
                console.log(e);
            }
        }
    });
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
    searchItems["conditions"] = getCheckedTreeNodes("condition-tree", conditionTree);
    searchItems["drugs"] = getCheckedTreeNodes("drug-tree", drugTree);
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
    if (loadedCnt >= 1) {
        $("#waiting").hide();
        loadedCnt = 0;
    } else {
        loadedCnt++;
    }
}

function showWaiting() {
    loadedCnt=0;
    $("#waiting").show();
}

function updateGraph() {
    graphDrawDetails = [];
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
    
    // // if only one leaf is checked, draw modifiers.
    // if (activeTabId == "#graph-tab-condition" && checkedNodes.length == 1 && checkedNodes[0].nodeChild.length == 0) {
    //     isModifier = true;
    // }

    let checkedModifierNodes;
    if (isModifier) {
        checkedModifierNodes = getCheckedTreeNodes("modifier-tree", modifierTree);
    }

    if (checkedModifierNodes && checkedModifierNodes.length > 0 && checkedModifierNodes[0].nodeId == "ROOT") {
        checkedModifierNodes = checkedModifierNodes[0].nodeChild;
    }

    // Update datatable
    updateDatatable(checkedNodes, checkedModifierNodes);

    // if checked only one category and has children, display the children
    if (checkedNodes.length == 1 && checkedNodes[0].nodeChild.length > 0) {
        checkedNodes = checkedNodes[0].nodeChild;
    }
    drawGraph(checkedNodes, checkedModifierNodes);
}

function updateDatatable(checkedNodes, checkedModifierNodes) {
    if (!checkedNodes || checkedNodes.length < 1) {
        emptyTable = true;
    }
    else {
        emptyTable = false;
        if (checkedNodes[0].nodeId == "ROOT") {
            graphStudyIds = graphSrcData["totalIds"];
        } else {
            graphStudyIds = [];
            checkedNodes.forEach(function(node) {
                let id = node.nodeId.substr(10);
                if (checkedModifierNodes) {
                    checkedModifierNodes.forEach(function(modifier) {
                        graphStudyIds = graphStudyIds.concat(graphSrcData[graphShowKey][id]['modifier'][modifier.nodeText]["studyIds"]);
                    });
                } else {
                    graphStudyIds = graphStudyIds.concat(graphSrcData[graphShowKey][id]["studyIds"]);
                }
            });
            graphStudyIds = graphStudyIds.filter((item, idx) => graphStudyIds.indexOf(item) == idx);
            if (graphStudyIds.length < 1) {
                emptyTable = true;
            }
        }
    }
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

    nodes.forEach(node => {
        let id = node.nodeId.substr(10);
        // if modifier, extract all data for modifiers.
        if (isModifier) {
            modifierNodes.forEach( modifier=> {
                let modifierName = modifier.nodeText;
                let nCnt = graphSrcData[graphShowKey][id]["count"][modifierName];
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
            // show all child node data
            let nCnt = graphSrcData[graphShowKey][id]["count"]["All"];
            // if (nCnt > 0) {
            graphLabels.push(node.nodeText);
            graphDrawData.push(nCnt);
            graphDrawDetails.push({node: node, cnt: nCnt});
            backgroundColors.push(bgColor[chartCnt % 7]);
            borderColors.push(bdColor[chartCnt % 7]);
            chartCnt++;
        }
    });
    chartGraph.data.labels = graphLabels;
    chartGraph.data.datasets[0].data = graphDrawData;
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
    conditionTree.uncheckAll();
    conditionTree.checkedNodes = ["CONDITION-141"];
    conditionTree.refresh();
    $('.nav-tabs a[href="#graph-tab-drug"]').tab('show');
    search();
}

function searchCancer() {
    conditionTree.uncheckAll();
    conditionTree.checkedNodes = ["CONDITION-44", "CONDITION-81"];
    conditionTree.refresh();
    search();
}