@extends('layouts.layout')
@section("title", $title )
@section("css")
<link href="{{ URL::asset('jstree/themes/default/style.min.css') }}" rel="stylesheet" />
<style>
    .jstree-default .jstree-themeicon,
    .jstree-default .jstree-themeicon-custom {
        color: #5ac70a;
        font-size: 131%;
        background-image: unset;
    }

    .jstree-closed>.jstree-ocl,
    .jstree-open>.jstree-ocl {
        background-image: unset;
    }

    .jstree-closed>.jstree-ocl::before,
    .jstree-open>.jstree-ocl::before,
    .jstree-default .jstree-themeicon::before {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        content: "\f054";
        color: #8b8b8b;
    }

    .jstree-default .jstree-themeicon::before {

        color: #5ac70a;
        content: "\f07b";
    }

    .jstree-open>.jstree-ocl::before {
        content: "\f078";
    }
</style>
@endsection
@section("js")
<script type="text/javascript" src="{{ URL::asset('jstree/jstree.js') }}"></script>
<script>
    var categories = <?php echo $categories ?>
</script>
<script>
    $(function() {
        var dataUpdate = [];
        var ajaxMethod = "GET";
        var jstree = $('#jstree');
        jstree.jstree({
            'core': {
                'data': categories,
                "animation": 0,
                "check_callback": function(op) {

                    if (op === "delete_node") {
                        return confirm("Are you sure you want to delete?");
                    }
                    return true;
                },
                "themes": {
                    "stripes": false,
                },
                "opened": true
            },
            "unique": {
                case_sensitive: true,
                trim_whitespace: true,
                duplicate: function(name, counter) {
                    console.info(name)
                    return name + "のコピー"; // This would just return the duplicate name to use as the node is created
                },
                "error_callback": function(n, p, f) {
                    console.info("Duplicate node `" + n + "` with function `" + f + "`!");
                }

            },
            "contextmenu": getContextmenu(),
            "plugins": [
                "contextmenu", "dnd", "search", "unique",
                "state", "wholerow"
            ],
        }).on("create_node.jstree", function(e, data) {
            dataUpdate = {
                action: "create_node",
                Category_ID: data.node.id,
                Sort_No: data.position,
                Parent_ID: data.parent ? data.parent : 0,
                Category_Nm: data.node.text
            };
            update(dataUpdate)
        }).on("move_node.jstree", function(e, data) {
            console.info(data)
            dataUpdate = {
                action: "move_node",
                Category_ID: data.node.id,
                Sort_No: data.position + 1,
                Old_Sort_No: data.old_position + 1,
                Parent_ID: data.parent ? data.parent : 0,
                Old_Parent_ID: data.old_parent,
                Category_Nm: data.node.text,
            };
            update(dataUpdate)
        }).on("rename_node.jstree", function(e, data) {
            dataUpdate = {
                action: "rename_node",
                Category_ID: data.node.id,
                Category_Nm: data.node.text
            };
            update(dataUpdate)
        }).on("delete_node.jstree", function(e, data) {
            dataUpdate = {
                action: "delete_node",
                Category_ID: data.node.id,
            };
            update(dataUpdate)
        });

        function getContextmenu() {
            return {
                "items": function($node) {
                    var tree = jstree.jstree(true);
                    return {
                        "Create": {
                            "separator_before": false,
                            "separator_after": false,
                            "label": "新規作成",
                            "icon": "fa fa-plus-square",
                            "action": function(obj) {
                                $node = tree.create_node($node);
                                tree.edit($node);
                            }
                        },
                        "Duplicate": {
                            "separator_before": false,
                            "separator_after": false,
                            "label": "複製",
                            icon: "fa fa-files-o",
                            "action": function(obj) {
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
                            "action": function(obj) {
                                tree.edit($node);
                            }
                        },
                        "Delete": {
                            "separator_before": false,
                            "separator_after": false,
                            "label": "削除",
                            icon: "fa fa-trash",
                            "action": function(obj) {
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
                    beforeSend: function() {
                        $('.loading').addClass('d-none');
                    },
                    success: function(res) {
                        console.info(res["data"])
                        if (res["status"]) {

                        } else {}
                    }
                });
            }
        }
    });
</script>
@endsection
@section('content')
<div id="jstree"></div>
{{ Form::hidden('route-cstore', route('c.store')) }}
@endsection