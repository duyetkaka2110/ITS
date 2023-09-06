$(function () {
    var ajaxMethod = "GET";
    var jstree = $('#jstree');
    jstree.jstree({
        'core': {
            'data': categories,
            "animation": 0,
            "dblclick_toggle": false,
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
            "contextmenu",
             "dnd", "search",
              "unique",
            "state", "wholerow"
        ],
    }).on('ready.jstree', function (e, data) {
        setScroll();
    }).on("create_node.jstree", function (e, data) {
        dataUpdate = {
            action: "create_node",
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
        update({
            action: "duplicate_node",
            Category_ID: getIdNode(data.original.id),
            Category_Nm: data.node.text,
            Parent_ID: data.parent ? getIdNode(data.parent) : 0,
            Sort_No: data.position + 1,
        })
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
    $(document).on("click", ".jstree-default .jstree-anchor", function () {
        var id = jstree.jstree('get_selected')[0];
        categorySelected = id;
        getListMitsumore(getIdNode(id))
        setScroll();
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
                var tree = jstree.jstree(true);
                return {
                    "Create": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "新規作成",
                        "icon": "fa fa-plus-square",
                        "action": function (obj) {
                            $node = tree.create_node($node);
                        }
                    },
                    "CreateRoot": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "ルートの新規作成",
                        "icon": "fa fa-plus-square",
                        "action": function (obj) {
                            $node = tree._model.data[0];
                            $node = tree.create_node($node);
                        }
                    },
                    "Duplicate": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": "複製",
                        icon: "fa fa-files-o",
                        "action": function (obj) {
                            inst = $.jstree.reference(obj.reference);
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
                        dispSuccessMsg(res["msg"])
                        if (dataUpdate.action == "create_node" || dataUpdate.action == "duplicate_node") {
                            var tree = jstree.jstree(true)
                            tree.settings.core.data = $.parseJSON(res["data"]);
                            jstree.jstree("refresh");
                        }
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
            url: $("input[name=route-mlist]").val() + "/" + id,
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
        if ($(".jstree-container-ul").width() > $(".mg-jstree").width()) {
            jstree.css("overflow-x", "scroll");
        } else {
            jstree.css("overflow-x", "unset");
        }
        if ($(".jstree-container-ul").height() > (window.innerHeight - 90 + $(".mg-menu").height() + 10)) {
            jstree.css("overflow-y", "scroll");
            jstree.css("height", window.innerHeight - 90);
        } else {
            jstree.css("overflow-y", "unset");
            jstree.css("height", "auto");
        }
    }
    var wrapper = document.querySelector('.mg-all');
    var box = null;
    var isHandlerDragging = false;
    var boxAminWidth = 200;
    var new_width = 0,
        current_width = 0;
    var box = $(".mg-jstree"),
        boxright = $(".mg-grid");

    document.addEventListener('mousedown', function (e) {
        // If mousedown event is fired from .handler, toggle flag to true
        if (e.target.classList.contains('handler')) {
            isHandlerDragging = true;
        }
    });

    document.addEventListener('mousemove', function (e) {
        // Don't do anything if dragging flag is false or 
        if (!isHandlerDragging) {
            $(".handler").removeClass("handler-active")
            return false;
        }

        $(".handler").addClass("handler-active")
        // save the current box width
        current_width = box.width();
        // check the minimum width
        new_width = e.clientX - box.offset().left - 8;
        if (new_width >= boxAminWidth  && ($(window).width() - new_width - 30) > boxAminWidth) {
            box.width(new_width);
            setScroll();
            boxright.css("max-width", $(window).width() - new_width - 30);
        }

        // make sure the boxs dont go past the wrapper, aka: the overflow effect
        //if they do, we recover the last width of the current box to keep the boxs inside the wrapper.
        if (wrapper.lastElementChild.offsetLeft + wrapper.lastElementChild.offsetWidth > wrapper.offsetWidth) {
            box.width(current_width);
        }

    });

    document.addEventListener('mouseup', function (e) {
        // Turn off dragging flag when user mouse is up
        isHandlerDragging = false;
    });
});