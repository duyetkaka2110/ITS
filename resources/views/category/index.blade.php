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
<script type="text/javascript" src="{{ URL::asset('jstree/jstree.min.js') }}"></script>
<script>
    var categories = <?php echo $categories ?>
</script>
<script>
    $(function() {
        var dataUpdate = [];
        var ajaxMethod = "GET";
        $('#jstree').jstree({
            'core': {
                'data': categories,
                "animation": 0,
                "check_callback": true,
                "themes": {
                    "stripes": false,
                },
            },
            "plugins": [
                "contextmenu", "dnd", "search",
                "state", "types", "wholerow", "changed"
            ],
        }).on("create_node.jstree", function(e, data) {
            console.log(data);
        }).on("move_node.jstree", function(e, data) {
            console.log(data);
            dataUpdate = {
                action: "move_node",
                id: data.node.id,
                position: data.position,
                parent: data.parent == "#" ? 0 : data.parent
            };
            // update(dataUpdate)
        }).on("copy_node.jstree", function(e, data) {
            console.log(data);
        }).on("rename_node.jstree", function(e, data) {
            console.log(data);
        }).on("delete_node.jstree", function(e, data) {
            console.log(data);
        }).on("paste.jstree", function(e, data) {
            console.log(data);
        });

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
                        console.info(res)
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