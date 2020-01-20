let studyTable;
let conditionTree;
let conditionSearchTree;
let searchItems;
let loadedCnt = 0;
let graphSrcData;
let graphDrawDetails;
let modifiers;
let chartGraph;
let isModifier;
let zoomIn = 1;
let bgColor = [
    "rgba(255, 99, 132, 0.2)",
    "rgba(255, 159, 64, 0.2)",
    "rgba(255, 205, 86, 0.2)",
    "rgba(75, 192, 192, 0.2)",
    "rgba(54, 162, 235, 0.2)",
    "rgba(153, 102, 255, 0.2)",
    "rgba(201, 203, 207, 0.2)"];
let bdColor = [
    "rgb(255, 99, 132)",
    "rgb(255, 159, 64)",
    "rgb(255, 205, 86)",
    "rgb(75, 192, 192)",
    "rgb(54, 162, 235)",
    "rgb(153, 102, 255)",
    "rgb(201, 203, 207)"];
let checkedProgramatically = false;

$(document).ready(function() {
    ej.base.enableRipple(true);
    initChart();
    initDatatable();
    initConditionTree();
    initSearchConditionTree();
    initDateRangePicker();
    initModifiers();
} );

function changeGraphSize(deltaZoom) {
    zoomIn += deltaZoom/2;
    if (zoomIn > 1) {
        $("#btn-zoom-out").prop("disabled", false);
    } else {
        zoomIn = 1;
        $("#btn-zoom-out").prop("disabled", true);
    }

    if (zoomIn < 5) {
        $("#btn-zoom-in").prop("disabled", false);
    } else {
        zoomIn = 5;
        $("#btn-zoom-in").prop("disabled", true);
    }
    let zoomPercent = 100 * zoomIn;
    $(".chart-container").css({
        "width": zoomPercent + "%"
    });
    chartGraph.update();
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
                searchKeys.manual_search = searchItems;
                return  $.extend(d, searchKeys);
            },
        },
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
            }
        };
          
        var ctx = document.getElementById("myChart").getContext("2d");
        chartGraph = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
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
        // nodeClicked: function(args) {
        //     var checkedNode = [args.node];
        //     if (args.event.target.classList.contains('e-fullrow') || args.event.key == "Enter") {
        //        var getNodeDetails = conditionTree.getNodeData(args.node);
        //         if (getNodeDetails.isChecked == 'true') {
        //             conditionTree.uncheckAll(checkedNode);
        //         } else {
        //             conditionTree.checkAll(checkedNode);
        //         }
        //     }
        // }
    });
    conditionTree.appendTo("#condition-tree");
}

function initSearchConditionTree() {
    conditionSearchTree = new ej.navigations.TreeView({
        fields: { id: 'nodeId', text: 'nodeText', child: 'nodeChild' },
        showCheckBox: true,
        nodeChecked: function() {
            if (!checkedProgramatically) {
                updateGraph();
            }
        }
    });
    conditionSearchTree.appendTo("#condition-serch-tree");
}

function readGraphData() {
    if (!searchItems) {
        readSearchItems();
    }
    checkedProgramatically = true;
    conditionSearchTree.fields.dataSource = searchItems["conditions"];
    conditionSearchTree.refresh();
    conditionSearchTree.checkAll();
    conditionSearchTree.expandAll();
    checkedProgramatically = false;
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
            } catch(e) {
                console.log(e);
            }
        }
    });
}

function search() {
    showWaiting();
    // Get search items
    readSearchItems();
    // Load table data
    studyTable.ajax.reload();
    // Load Graph Data
    readGraphData();
    $("#search-modal").modal("hide");
}

function readSearchItems() {
    searchItems = getFormData($("#search-other-form"));
    searchItems["conditions"] = getCheckedConditions("condition-tree");
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

    return indexed_array;
}

function getCheckedConditions(selector) {
    let checkedNodes = getCheckedNodes(selector);
    
    // console.log("function:", checkedNodes);
    checkedNodes.forEach(element => {
        let nodeObject = conditionTree.getNodeObject(element);
        removeChildren(nodeObject.nodeChild, checkedNodes);
    });
    
    checkedNodes.forEach((element, index) => {
        checkedNodes[index] = conditionTree.getNodeObject(element);
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

    let checkedNodes = getCheckedConditions("condition-serch-tree");
    isModifier = $("#graph-modifier").is(":checked");

    // if only one leaf is checked, draw modifiers.
    if (checkedNodes.length == 1 && checkedNodes[0].nodeChild.length == 0) {
        isModifier = true;
    }
    // if checked only one category and has children, display the children
    if (checkedNodes.length == 1 && checkedNodes[0].nodeChild.length > 0) {
        drawGraph(checkedNodes[0].nodeChild);
        return;
    }

    drawGraph(checkedNodes);
}

function drawGraph(nodes) {
    let graphLabels = [];
    let graphDrawData = [];
    let backgroundColors = [];
    let borderColors = [];
    let chartCnt = 0;

    nodes.forEach(node => {
        // if modifier, extract all data for modifiers.
        let id = node.nodeId.substr(10);
        if (isModifier) {
            modifiers.forEach( modifier=> {
                let nCnt = graphSrcData[id]["count"][modifier];
                if (nCnt > 0) {
                    graphLabels.push(node.nodeText + " - " + modifier);
                    graphDrawData.push(nCnt);
                    graphDrawDetails.push({node: node, modifier: modifier, cnt: nCnt});
                    backgroundColors.push(bgColor[chartCnt % 7]);
                    borderColors.push(bdColor[chartCnt % 7]);
                    chartCnt++;
                }
            });
        } else {    // else, extract all child node data
            let nCnt = graphSrcData[id]["count"]["All"];
            if (nCnt > 0) {
                graphLabels.push(node.nodeText);
                graphDrawData.push(nCnt);
                graphDrawDetails.push({node: node, cnt: nCnt});
                backgroundColors.push(bgColor[chartCnt % 7]);
                borderColors.push(bdColor[chartCnt % 7]);
                chartCnt++;
            }
        }
    });
    chartGraph.data.labels = graphLabels;
    chartGraph.data.labels = graphLabels;
    chartGraph.data.datasets[0].data = graphDrawData;
    chartGraph.data.datasets[0].backgroundColor = backgroundColors;
    chartGraph.data.datasets[0].borderColor = borderColors;
    console.log(backgroundColors);
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
                } catch (e) {
                    console.log(e);
                }
            }
        }
    });
}