@extends('layouts.layout')
@section("title", $title )
@section("css")
<link href="{{ URL::asset('jstree/themes/default/style.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('css/category.css') }}" rel="stylesheet" />
@endsection
@section("js")
<script type="text/javascript" src="{{ URL::asset('jstree/jstree.min.js') }}"></script>
<script>
    var categories = <?php echo $categories ?>
</script>
<script type="text/javascript" src="{{ URL::asset('js/category.js') }}"></script>
@endsection
@section('content')
<div id="jstree"></div>

<a href="{{ route('c.reset') }}" class="btn btn-primary m-3">データ再設定</a>
{{ Form::hidden('route-cstore', route('c.store')) }}
@endsection