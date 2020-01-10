ej.base.enableRipple(true);
var conditionCnt = 1000;
var managedConditions = [];

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
    createTree("Unmanaged Conditions", [], 0);
    loadUnmanagedConditions(1);
    loadManagedConditions();
//    createTree("Condtion1", org_data, 1);
});

function deleteNode() {
	let selectedNodes = treeViewInstances[0].selectedNodes;
	treeViewInstances[0].removeNodes(selectedNodes);
	
	// Call remove api
}

function CreateManagedConditionTrees() {
    managedConditions.forEach(element => createTree(element["category"], element["conditions"], element["id"]));
}

function createTree(title, data, nIdx) {
    if (nIdx > 0) {
        let treeHtml = '<div class="col-12 col-lg-6 padding-5"><div class="box"><h5 class="tree-title">' + title + '</h5>';
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

            // if droppedIdx is 0, it means that it was dropped on the parent of the tree.
            // if category is 0, it means it's in unmanaged condition (parentID == -1)
            // if category is greater than 0, it means it's in managed condition (parentId == 0)
            let dropedCategory = getDropedTreeID(args); 

            // if dragged node has children, it's category must be changed too.
            let hasChildren = args.draggedNodeData.hasChildren;

            if (droppedIdx !== 0) {
                let dropedNodeDepth = calculateNodeDepth(dropedCategory, droppedIdx);
                if (args.dropLevel == dropedNodeDepth) {
                    droppedIdx = args.droppedNodeData.parentID;
                    if (droppedIdx === null) {
                        droppedIdx = 0;
                    }
                }
            }
            //Call update node parent
            $.ajax({
                type: "POST",
                url: "update_condition.php",
                data: {currentId: draggedIdx, parentId: droppedIdx, category: dropedCategory, hasChildren: hasChildren,  action: "UPDATE PARENT"}
            });
            return true;
        },
        nodeEdited: function(args) {
            if (args.cancel) {
                return false;
            }
            
            let editedId = args.nodeData.id;
            let newText = args.newText;
            
            // Call Update Node data api
            $.ajax({
                type: "POST",
                url: "update_condition.php",
                data: {editedId: editedId, newText: newText, action: "UPDATE TEXT"}
            });
            return true;
        },
    
        nodeDragStop: function(args) {
            let droppedTreeID = getDropedTreeID(args);
            // Prevent make child in unmanaged tree
            if ( droppedTreeID==-1 || (droppedTreeID== 0 && args.dropLevel > 1) ) {
                args.cancel = true;
            }
            //in unmanaged tree, Prevent drop child, which has child node
            if (droppedTreeID == 0 && args.draggedNodeData.hasChildren) {
                args.cancel = true;
            }
        },
        dataSourceChanged: function(args){
            let aaa = 0;
        }
    });

    treeViewInstances[nIdx].appendTo("#tree-"+ nIdx);
    treeViewInstances[nIdx].expandAll();
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

    loadUnmanagedConditions();
}

function nextPage() {
    let page = $("#page").val();
    page++;
    $("#prev-page").prop("disabled", false);
    $("#page").val(page);

    loadUnmanagedConditions();
}

function loadUnmanagedConditions() {
    let searchKey = $("#search").val();
    let pageNum = $("#page").val();
    let data = {page: pageNum, cnt: conditionCnt, search: searchKey};
    $.ajax({
        type: "POST",
        url: "get_unmanaged_condition.php",
        data: data,
        success: function(response) {
            if (!response) {
                $("next-page").prop("disabled", true);
                return;
            }
            let conditions = JSON.parse(response);
            treeViewInstances[0].fields.dataSource = conditions;
            if (conditions.length < conditionCnt) {
                $("#next-page").prop("disabled", true);
            }
        }
      });
}

function loadManagedConditions() {
    $.ajax({
        type: "POST",
        url: "get_managed_condition.php",
        success: function(response) {
            if (!response) {
                return;
            }
            managedConditions = JSON.parse(response);
            CreateManagedConditionTrees();
        }
      });
}

function search() {
    $("#page").val(1);

    loadUnmanagedConditions();
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
            let newCategory = {id: response["category"], category: categoryName, conditions: [{nodeId: response["condition"], nodeText: categoryName}]};
            managedConditions.push(newCategory);
            createTree(newCategory["category"], newCategory["conditions"], newCategory["id"]);

            $("#create-category-dlg").modal("hide");

            loadUnmanagedConditions();
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