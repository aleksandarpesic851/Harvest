let studyTable;
let conditionTree;
let conditionSearchTree;
let modifiers = ["Relapsed", "Refractory", "Chronic", "Acute", "High-risk", "Healthy", "Systemic", "Resistance", "Advanced", "Metastases",
                "Myocardial Infarction", "Smoldering", "Atopic", "Progression", "Recurrent", "Adult", "Child"];
let searchItems;
let loadedCnt = 0;

$(document).ready(function() {
    ej.base.enableRipple(true);

    initChart();
    initDatatable();
    initConditionTree();
    initSearchConditionTree();
    changeCanvasSize();
    initDateRangePicker();
} );

function changeCanvasSize() {
    $(".chart-container").css({
        "min-width": "3000px"
    });
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
            { data: "nct_id"},
            { data: "title"},
            { data: "enrollment"},
            { data: "status"},
            { data: "study_types"},
            { data: "conditions"},
            { data: "interventions"},
            { data: "outcome_measures", width: "30%"},
            { data: "phases"},
            { data: "study_designs"},
        ],
        order: [[ 0, 'asc' ]]
    });
}

function initChart() {
    let backgroundColor = [
        "rgba(255, 99, 132, 0.2)",
        "rgba(255, 159, 64, 0.2)",
        "rgba(255, 205, 86, 0.2)",
        "rgba(75, 192, 192, 0.2)",
        "rgba(54, 162, 235, 0.2)",
        "rgba(153, 102, 255, 0.2)",
        "rgba(201, 203, 207, 0.2)"];
    let borderColor = [
        "rgb(255, 99, 132)",
        "rgb(255, 159, 64)",
        "rgb(255, 205, 86)",
        "rgb(75, 192, 192)",
        "rgb(54, 162, 235)",
        "rgb(153, 102, 255)",
        "rgb(201, 203, 207)"];

        var data = {
            labels: ["x1", "x2", "x3","x1", "x2", "x3","x1", "x2", "x3","x1", "x2", "x3"],
            datasets: [{
                label: "First",
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 1,
                data: [10, 20, 30],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Second",
                backgroundColor: 'rgba(99, 132, 255, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Third",
                backgroundColor: 'rgba(99, 255, 132, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Fourth",
                backgroundColor: 'rgba(0, 0, 0, 0.2)',
                borderWidth: 1,
                data: [5, 30, 35],
                xAxisID: "bar-x-axis1",
            }, {
                label: "Total",
                backgroundColor: 'rgba(100, 100, 100, 0.2)',
                borderWidth: 1,
                data: [30, 30, 35],
                xAxisID: "bar-x-axis2",
            }]
        };
          
        var options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: "right"
            },
            responsiveAnimationDuration: 200,
            scales: {
                xAxes: [
                {
                    id: "bar-x-axis1",
                    //barThickness: 30,
                    barPercentage: 0.9,
                    categoryPercentage: 0.6,
                    offset: true
                }, 
                {
                    display: false,
                    stacked: true,
                    id: "bar-x-axis2",
                    //barThickness: 330,
                    // these are needed because the bar controller defaults set only the first x axis properties
                    offset: true,
                    barPercentage: 0.9,
                    categoryPercentage: 0.8
                }],
                yAxes: [{
                    stacked: false,
                    ticks: {
                        beginAtZero: true
                    },
                }]
            }
        };
          
        var ctx = document.getElementById("myChart").getContext("2d");
        var myBarChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
          
        
    // var chart = new Chart(document.getElementById("myChart"),
    //     {
    //         type:"bar",
    //         data:
    //         {
    //             labels:["Paris", "London", "Mumbai"],
    //             datasets:[
    //             {   
    //                 label:"Store",
    //                 data:[15, 20, 25],
    //                 fill:false,
    //                 backgroundColor:backgroundColor,
    //                 borderColor:borderColor,
    //                 borderWidth:1,
    //                 xAxisID: "bar-x-axis2"
    //             },
    //             {   
    //                 label:"Online",
    //                 data:[30, 25, 15],
    //                 fill:false,
    //                 backgroundColor:backgroundColor,
    //                 borderColor:borderColor,
    //                 borderWidth:1,
    //                 xAxisID: "bar-x-axis2"
    //             },
    //             {   
    //                 label:"Total",
    //                 data:[30, 40, 50],
    //                 fill:false,
    //                 backgroundColor:'rgba(0,0,0,0.5)',
    //                 borderColor:borderColor,
    //                 borderWidth:1,
    //                 xAxisID: "bar-x-axis1"
    //             }]
    //         },
    //         options: {
    //             responsive: true,
    //             legend: {
    //                 display: true,
    //                 position: "right"
    //             },
    //             responsiveAnimationDuration: 200,
    //             scales: {
    //                 xAxes: [
    //                     {
    //                         display: false,
    //                         scaleLabel: {
    //                             display: true,
    //                             fontSize: 15
    //                         },
    //                         categoryPercentage: 0.5,
    //                         barPercentage: 0.5,
    //                         id: "bar-x-axis2"
    //                     },
    //                     {
    //                         display: true,
    //                         scaleLabel: {
    //                             display: true,
    //                             fontSize: 15,
    //                         },
    //                         id: "bar-x-axis1",
    //                         categoryPercentage: 0.4,
    //                         barPercentage: 1,
    //                         gridLines: {
    //                             offsetGridLines: true
    //                         },
    //                     }
    //                 ],
    //                 yAxes: [{
    //                     display: true,
    //                     scaleLabel: {
    //                         display: true,
    //                         fontSize: 15,
    //                         labelString: "Number of studies"
    //                     },
    //                     ticks: {"beginAtZero":true},
    //                 }]
    //             },
    //         }
    //     });
    //updateDisplayData();
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
        showCheckBox: true
    });
    conditionSearchTree.appendTo("#condition-serch-tree");
}

function readGraphData() {
    if (!searchItems) {
        readSearchItems();
    }
    conditionSearchTree.fields.dataSource = searchItems["conditions"];
    conditionSearchTree.refresh();
    conditionSearchTree.checkAll();
    conditionSearchTree.expandAll();

    //load graph data
    
    hideWaiting();
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
    searchItems["conditions"] = getCheckedConditions();
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

function getCheckedConditions() {
    let checkedNodes = getCheckedNodes("condition-tree");
    
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