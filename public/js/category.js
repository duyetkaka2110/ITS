
$(function () {
    var dataUpdate = [];
    var ajaxMethod = "GET";
    var jstree = $('#jstree');
    jstree.jstree({
        'core': {
            'data': categories,
            "animation": 0,
            "dblclick_toggle" : false,
            "check_callback": function (op) {
                if (op === "delete_node") {
                    return confirm("削除します。よろしいですか？");
                }
                return true;
            },
            "themes": {
                "stripes": false,
            }
        },
        "contextmenu": getContextmenu(),
        "plugins": [
            "contextmenu", "dnd", "search", "unique",
            "state", "wholerow"
        ],
    }).on('ready.jstree', function (e, data) {
        setScroll();
    }).on("create_node.jstree", function (e, data) {
        dataUpdate = {
            action: "create_node",
            Category_ID: getIdNode(data.node.id),
            Sort_No: data.position + 1,
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Category_Nm: data.node.text
        };
        update(dataUpdate)
    }).on("move_node.jstree", function (e, data) {
        dataUpdate = {
            action: "move_node",
            Category_ID: getIdNode(data.node.id),
            Sort_No: data.position + 1,
            Old_Sort_No: data.old_position + 1,
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Old_Parent_ID: getIdNode(data.old_parent),
            Category_Nm: data.node.text,
        };
        update(dataUpdate)
    }).on("rename_node.jstree", function (e, data) {
        dataUpdate = {
            action: "rename_node",
            Category_ID: getIdNode(data.node.id),
            Category_Nm: data.node.text
        };
        update(dataUpdate)
    }).on("delete_node.jstree", function (e, data) {
        dataUpdate = {
            action: "delete_node",
            Category_ID: getIdNode(data.node.id),
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Sort_No: data.position + 1,
        };
        update(dataUpdate)
    }).on("copy_node.jstree", function (e, data) {
        console.info(data)
        let list = [{
            Category_ID: getIdNode(data.node.id),
            Category_Nm: data.node.text,
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Sort_No: data.position + 1,
            Category_ID_Old: getIdNode(data.original.id)
        }];
        let node, parent;
        $.each(data.node.children_d, function (index, key) {
            node = data.instance._model.data[key];
            parent = data.instance._model.data[data.instance._model.data[key].parent];
            list.push({
                Category_ID: getIdNode(data.instance._model.data[key].id),
                Category_Nm: data.instance._model.data[key].text,
                Parent_ID: getIdNode(data.instance._model.data[key].parent),
                Sort_No: ($.inArray(node.id, parent.children)) + 1,
                Category_ID_Old: getIdNode(data.original.children_d[index])
            })
        })
        update({
            action: "duplicate_node",
            list: list,
            Category_ID: getIdNode(data.node.id),
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Sort_No: data.position + 1,
        })
    }).on("dblclick.jstree", function (event, data) {
        var id = jstree.jstree('get_selected')[0];
        var node = jstree.jstree("get_node", id)
        if (node.parent != 0) {
            categorySelected = id;
            getListMitsumore(id)
        }
    }).on("click.jstree",function(){
        setScroll();
    });
    $(window).resize(function () {
        setScroll();
    })
    if (Cookies.get("cate-close") != undefined && Cookies.get("cate-close") == "true") {
        $(".mg-all").addClass("cate-close");
    }
    $(".mg-all .btn-collapse").on("click", function () {
        $(".mg-all").toggleClass("cate-close");
        Cookies.set("cate-close", $(".mg-all").hasClass("cate-close"));
    })
    function getIdNode(id) {
        idnew = id.split("_");
        if (idnew.length > 1) {
            return idnew[1];
        }
        return id;
    }

    function getContextmenu() {
        return {
            "items": function ($node) {
                var tree = jstree.jstree();
                return {
                    "Create": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "新規作成",
                        "icon": "fa fa-plus-square",
                        "action": function (obj) {
                            $node = tree.create_node($node);
                            tree.edit($node);
                        }
                    },
                    "Duplicate": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "複製",
                        icon: "fa fa-files-o",
                        "action": function (obj) {
                            inst = $.jstree.reference(obj.reference),
                                obj = inst.get_node(obj.reference);
                            tree.copy(obj);
                            obj = inst.get_node(jstree.find("[id='" + obj.parent + "']"));
                            tree.paste(obj);
                        }
                    },
                    "Rename": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "フォルダ名の変更",
                        icon: "fa fa-text-height",
                        "action": function (obj) {
                            tree.edit($node);
                        }
                    },
                    "Delete": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "削除",
                        icon: "fa fa-trash",
                        "action": function (obj) {
                            tree.delete_node($node);
                        }
                    }
                };
            }
        }
    }

    function update(dataUpdate) {
        if (dataUpdate) {
            $.ajax({
                type: ajaxMethod,
                data: dataUpdate,
                url: $("input[name=route-cstore]").val(),
                beforeSend: function () {
                    $('.loading').addClass('d-none');
                },
                success: function (res) {
                    if (res["status"]) {
                    } else {
                        dispMessageModal(res["msg"])
                    }
                }
            });
            setScroll();
        }
    }
    function getListMitsumore(id) {
        // 見積明細取得
        $.ajax({
            type: ajaxMethod,
            url: $("input[name=route-mlist]").val()+"/"+id,
            success: function (res) {
                if (res["status"]) {
                    flex.itemsSource = new wijmo.collections.ObservableArray($.parseJSON(res["data"]));
                } else {
                    dispMessageModal(res["msg"])
                }
            }
        });
    }
    function setScroll() {
        if ($(".jstree-container-ul").width() > 250) {
            jstree.css("overflow-x", "scroll");
        } else {
            jstree.css("overflow-x", "unset");
        }
        if ($(".jstree-container-ul").height() > ( window.innerHeight - 90 + $(".mg-menu").height() + 10)) {
            jstree.css("overflow-y", "scroll");
            jstree.css("height", window.innerHeight - 90);
        } else {
            jstree.css("overflow-y", "unset");
            jstree.css("height", "auto");
        }
    }
});