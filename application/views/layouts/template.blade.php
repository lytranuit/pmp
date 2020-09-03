<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9 ]><html class="ie9 no-js"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html>
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    @if($template == "admin")
    <meta name="viewport" content="width=1280" />
    @else
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @endif
    <meta name="description" content="Đồ ăn vặt , nước uống trà sữa sinh tố các loại địa điểm chợ f7 Tp Tuy Hòa Phú Yên" />
    <meta name="keywords" content="do an,nuoc uong,do an vat tuy hoa,tra sua tuy hoa,sinh to tuy hoa, do chien tuy hoa">
    <meta property="og:url" content="{{current_url()}}" />
    <meta property="og:title" content="Đầu Bư Quán" />
    <meta property="og:type" content="article" />
    <meta property="og:description" content="Đồ ăn vặt, nước uống trà sữa sinh tố các loại địa điểm chợ f7 Tp Tuy Hòa Phú Yên" />
    <meta property="og:image" content="<?= base_url() ?>public/img/thumbnail.png?v=1" />

    <link rel="canonical" href="{{base_url()}}" />

    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url() ?>public/img/favicon.png" />
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
</head>

<body>

    <div class="wrapper-container">
        <!--HEADER-->
        <!--END HEADER-->

        <!--MAIN CONTENT-->
        @yield("content")
        <!--END MAIN CONTENT-->

        <!--FOTTER-->
        <?= $widget->footer() ?>
        <!--END FOTTER-->
    </div>

</body>



</html>