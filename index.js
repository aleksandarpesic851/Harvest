let studyTable;
let conditionTree;
let modifiers = ["Relapsed", "Refractory", "Chronic", "Acute", "High-risk", "Healthy", "Systemic", "Resistance", "Advanced", "Metastases",
                "Myocardial Infarction", "Smoldering", "Atopic", "Progression", "Recurrent", "Adult", "Child"];

$(document).ready(function() {
    initChart();
    initDatatable();
    initConditionTree();
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
                return  $.extend(d, getSearchItems());
             }
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
                } catch (e) {
                    console.log(e);
                }
            }
        }
    });

    ej.base.enableRipple(true);
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

function search() {
    studyTable.ajax.reload();
    $("#search-modal").modal("hide");
}

function getSearchItems() {
    let searchItems = {};
    searchItems.manual_search = getFormData($("#search-other-form"));
    return searchItems;
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