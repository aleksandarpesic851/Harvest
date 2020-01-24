ej.base.enableRipple(true);
var drugCnt = 100;
var managedDrugs = [];
var draggedTreeId = -1;
//define the array of JSON
var org_data = [
    {
        nodeId: '01', nodeText: 'Music',
        nodeChild: [
            { nodeId: '01-01', nodeText: 'Gouttes.mp3' }
        ]
    },
    {
        nodeId: '02', nodeText: 'Videos', expanded: true,
        nodeChild: [
            { nodeId: '02-01', nodeText: 'Naturals.mp4' },
            { nodeId: '02-02', nodeText: 'Wild.mpeg' },
        ]
    },
    {
        nodeId: '03', nodeText: 'Documents',
        nodeChild: [
            { nodeId: '03-01', nodeText: 'Environment Pollution.docx' },
            { nodeId: '03-02', nodeText: 'Global Water, Sanitation, & Hygiene.docx' },
            { nodeId: '03-03', nodeText: 'Global Warming.ppt' },
            { nodeId: '03-04', nodeText: 'Social Network.pdf' },
            { nodeId: '03-05', nodeText: 'Youth Empowerment.pdf' },
        ]
    },
    {
        nodeId: '05', nodeText: 'Music',
        nodeChild: [
            { nodeId: '05-01', nodeText: 'Gouttes.mp3' }
        ]
    },
    {
        nodeId: '06', nodeText: 'Videos', expanded: true,
        nodeChild: [
            { nodeId: '06-01', nodeText: 'Naturals.mp4' },
            { nodeId: '06-02', nodeText: 'Wild.mpeg' },
        ]
    },
    {
        nodeId: '07', nodeText: 'Documents',
        nodeChild: [
            { nodeId: '07-01', nodeText: 'Environment Pollution.docx' },
            { nodeId: '07-02', nodeText: 'Global Water, Sanitation, & Hygiene.docx' },
            { nodeId: '07-03', nodeText: 'Global Warming.ppt' },
            { nodeId: '07-04', nodeText: 'Social Network.pdf' },
            { nodeId: '07-05', nodeText: 'Youth Empowerment.pdf' },
        ]
    },
];

var treeViewInstances = [];

$(function() {
    createTree("Unmanaged Drugs", [], 0);
    loadUnmanagedDrugs(1);
    loadManagedDrugs();
    hideWaiting();
//    createTree("Condtion1", org_data, 1);
});

function deleteNode() {
	let selectedNodes = treeViewInstances[0].selectedNodes;
	treeViewInstances[0].removeNodes(selectedNodes);
	
	// Call remove api
}

function CreateManagedDrugTrees() {
    managedDrugs.forEach(element => createTree(element["category"], element["drugs"], element["id"]));
}

function createTree(title, data, nIdx) {
    if (nIdx > 0) {
        let treeHtml = '<div class="col-12 col-lg-6 padding-5" id="tree-container-' + nIdx + '"><div class="box">';
        treeHtml += '<h5 class="tree-title edit" id="' + nIdx + '">' + title + '</h5>';
        treeHtml += '<div class="row" style="text-align: right; padding-right: 20px; padding-bottom: 5px">';
        treeHtml += '<button class="ml-auto btn btn-danger" title="Delete This Category" onclick="deleteCategory(' + nIdx + ')">';
        treeHtml += '<i class="fa fa-trash" aria-hidden="true"></i></button></div>';
        treeHtml += '<div class="small-box tree-box" id="tree-' + nIdx + '"></div></div></div>';
    
        $("#managed-trees").append(treeHtml);
    }

    treeViewInstances[nIdx] = new ej.navigations.TreeView({

        allowDragAndDrop: true,
    
        allowDropSibling: true, // allows to drop sibling
    
        allowDropChild: false, // allows to drop as child
        
//        allowMultiSelection: true,
        
        allowEditing: true,
    
        fields: { dataSource: data, id: 'nodeId', text: 'nodeText', child: 'nodeChild', category: "nodeCategory" },
        
        nodeDropped: function(args) {
            if (args.cancel) {
                return false;
            }
            
            let draggedIdx = args.draggedNodeData.id;
            let droppedIdx = 0;
            if (args.droppedNodeData) {
                droppedIdx = args.droppedNodeData.id;
            }

            let droppedTreeId = getDropedTreeID(args);

            // if droppedIdx is 0, it means that it was dropped on the parent of the tree.
            // if category is 0, it means it's in unmanaged drug (parentID == -1)
            // if category is greater than 0, it means it's in managed drug (parentId == 0)

            // // if dragged node has children, it's category must be changed too.
            // let hasChildren = args.draggedNodeData.hasChildren;

            if (droppedIdx !== 0) {
                let dropedNodeDepth = calculateNodeDepth(droppedTreeId, droppedIdx);
                if (args.dropLevel == dropedNodeDepth) {
                    droppedIdx = args.droppedNodeData.parentID;
                    if (droppedIdx === null) {
                        droppedIdx = 0;
                    }
                }
            }

            let draggedParent = args.draggedNodeData.parentID;
            //Call update node parent
            $.ajax({
                type: "POST",
                url: "update_drug.php",
                data: {currentId: draggedIdx, parentId: droppedIdx, category: droppedTreeId, prev_parentId: draggedParent, prev_category: draggedTreeId, action: "UPDATE PARENT"},
                success: function(response) {
                    reloadChangedTree(draggedTreeId);
                    reloadChangedTree(droppedTreeId);
                }
            });
            return true;
        },
        nodeEdited: function(args) {
            if (args.cancel) {
                return false;
            }
            
            let editedId = args.nodeData.id;
            let newText = args.newText;
            let categoryId = nIdx;

            // Call Update Node data api
            $.ajax({
                type: "POST",
                url: "update_drug.php",
                data: {editedId: editedId, newText: newText, categoryId: categoryId, action: "UPDATE TEXT"}
            });
            return true;
        },
    
        nodeDragStop: function(args) {
            let droppedIdx = getDropedTreeID(args);
            // Prevent make child in unmanaged tree
            if (droppedIdx == -1 || (droppedIdx== 0 && args.dropLevel > 1) ){
                args.cancel = true;
                return;
            }
            //Prevent drop child, which has child node between other trees
            if (droppedIdx != draggedTreeId && args.draggedNodeData.hasChildren) {
                args.cancel = true;
            }
        },

        nodeDragStart: function(args) {
            draggedTreeId = nIdx;
        },

    });

    treeViewInstances[nIdx].appendTo("#tree-"+ nIdx);

    initEditables();
}

function prevPage() {
    let page = $("#page").val();
    if (page < 2) {
        return;
    }

    page--;
    if (page == 1) {
        $("#prev-page").prop("disabled", true);
    }
    $("#page").val(page);
    $("#prev-page").prop("disabled", false);

    loadUnmanagedDrugs();
}

function nextPage() {
    let page = $("#page").val();
    page++;
    $("#prev-page").prop("disabled", false);
    $("#page").val(page);

    loadUnmanagedDrugs();
}

function loadUnmanagedDrugs() {
    let searchKey = $("#search").val();
    let pageNum = $("#page").val();
    let data = {page: pageNum, cnt: drugCnt, search: searchKey};
    $.ajax({
        type: "POST",
        url: "get_unmanaged_drug.php",
        data: data,
        success: function(response) {
            if (!response) {
                $("next-page").prop("disabled", true);
                return;
            }
            let drugs = JSON.parse(response);
            treeViewInstances[0].fields.dataSource = drugs;
            if (drugs.length < drugCnt) {
                $("#next-page").prop("disabled", true);
            } else {
                $("#next-page").prop("disabled", false);
            }
        }
      });
}

function loadManagedDrug(id) {
    let data = {treeId: id};
    $.ajax({
        type: "POST",
        url: "get_managed_drug.php",
        data: data,
        success: function(response) {
            let drugs = JSON.parse(response);
            treeViewInstances[id].fields.dataSource = drugs;
        }
      });
}

function loadManagedDrugs() {
    $.ajax({
        type: "POST",
        url: "get_managed_drug.php",
        success: function(response) {
            if (!response) {
                return;
            }
            managedDrugs = JSON.parse(response);
            CreateManagedDrugTrees();
        }
      });
}

function reloadChangedTree(id) {
    if (id == 0) {
        loadUnmanagedDrugs();
    } else {
        loadManagedDrug(id);
    }
}

function search() {
    $("#page").val(1);
    $("#prev-page").prop("disabled", true);

    loadUnmanagedDrugs();
}

function getDropedTreeID(args) {
    let htmlId = $(args.droppedNode).closest("ul").closest("div").prop("id");   //tree-0
    if (!htmlId) {
        return -1;
    }
    return htmlId.substr(5);                                                    //0
}

function getDraggedTreeID(args) {
    let htmlId = $(args.draggedNode).closest("ul").closest("div").prop("id");
    return htmlId.substr(5);
}

function createCategory() {
    let categoryName = $("#category-name").val();
    if (!categoryName) {
        alert("Please type category name");
        return;
    }

    $.ajax({
        type: "POST",
        url: "manage_category.php",
        data: {category: categoryName, action: "Create"},
        success: function(response) {
            if (response == "exist") {
                alert("There is same category already. Please try other category.");
                return;
            }
            
            response = JSON.parse(response);
            let newCategory = {id: response["category"], category: categoryName, drugs: [{nodeId: response["drug"], nodeText: categoryName}]};
            managedDrugs.push(newCategory);
            createTree(newCategory["category"], newCategory["drugs"], newCategory["id"]);

            $("#create-category-dlg").modal("hide");

            loadUnmanagedDrugs();
        }
    });
}

function showCreateCategoryDlg() {
    $("#category-name").val("");
    $("#create-category-dlg").modal("show");
}

// Calculate Node depth by id
function calculateNodeDepth(categoryId, nodeId) {
    let depth = 0;
    let searchingNode = $("#tree-" + categoryId).find(`[data-uid='` + nodeId + `']`)[0];
    while(searchingNode) {
        searchingNode = searchingNode.closest("ul").closest("li");
        depth++;
    }
    return depth;
}

function initEditables() {
    $('.edit').editable('manage_category.php', {
        indicator : 'Saving…',
        event     : 'click',
        submit    : 'Save',
        tooltip   : 'Click to edit…'
    });
}

function deleteCategory(id) {
    if (confirm("Are you sure to delete this category?")) {
        $.ajax({
            type: "POST",
            url: "manage_category.php",
            data: {action: "Delete", id: id},
            success: function(response) {
                if (response == "delete_ok") {
                    $("#tree-container-" + id).remove();
                }
            }
        });
    }
}

function calculateStudyDrug() {
    showWaiting();
    $.ajax({
        type: "POST",
        url: "calculate_control.php",
        data: {post: true},
        success: function(response) {
            hideWaiting();
            if (response == "ok") {
                alert("Calculation Started in Background!");
            } else {
                alert("Error: " + response);
            }
        }
    });
}

function hideWaiting() {
    $("#waiting").hide();
}

function showWaiting() {
    $("#waiting").show();
}