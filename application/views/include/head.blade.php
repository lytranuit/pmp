<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="canonical" href="{{base_url()}}">

<link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>public/img/favicon.png">
<title>
    @section("title") 
    {{$project_name}}
    @show
</title>


<!-- core CSS -->
@foreach($stylesheet_tag as $url)
<link href="{{$url}}" rel="stylesheet">
@endforeach
<script>
    var path = '<?= base_url() ?>';
</script>
@foreach($javascript_tag as $url)
<script src="{{$url}}"></script>
@endforeach