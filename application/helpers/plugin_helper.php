<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
if (!function_exists('load_inputfile')) {

    function load_inputfile(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/lib/fileinput/css/fileinput.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/fileinput/css/theme_fileinput.css");

        array_push($data['javascript_tag'], base_url() . "public/lib/fileinput/js/fileinput.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/fileinput/js/sortable.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/fileinput/js/theme_fileinput.js");
    }
}


if (!function_exists('load_datatable')) {

    function load_datatable(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/admin/vendor/datatables/datatables.min.css");

        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/datatables/datatables.min.js");
        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/datatables/jquery.highlight.js");
    }
}


if (!function_exists('load_daterangepicker')) {

    function load_daterangepicker(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/lib/daterangepicker/daterangepicker.css");

        array_push($data['javascript_tag'], base_url() . "public/lib/daterangepicker/moment.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/daterangepicker/daterangepicker.js");
    }
}

if (!function_exists('load_orgchart')) {

    function load_orgchart(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/lib/orgchart/css/jquery.orgchart.css");

        array_push($data['javascript_tag'], base_url() . "public/lib/orgchart/js/jquery.orgchart.js");
    }
}




if (!function_exists('load_editor')) {

    function load_editor(&$data)
    {

        //        array_push($data['stylesheet_tag'], "https://cdn.jsdelivr.net/npm/froala-editor@2.9.3/css/froala_editor.min.css");
        //        array_push($data['stylesheet_tag'], "https://cdn.jsdelivr.net/npm/froala-editor@2.9.3/css/froala_style.min.css");
        //        array_push($data['javascript_tag'], "https://cdn.jsdelivr.net/npm/froala-editor@2.9.3/js/froala_editor.min.js");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/froala_editor.min.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/froala_style.min.css");
        /////////// Plugin
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/char_counter.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/code_view.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/colors.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/draggable.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/emoticons.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/file.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/fullscreen.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/image.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/image_manager.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/line_breaker.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/quick_insert.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/table.css");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/video.css");

        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/froala_editor.min.js");
        /////////// Plugin
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/align.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/char_counter.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/colors.min.js");
        array_push($data['stylesheet_tag'], base_url() . "public/lib/froala_editor/plugins/file.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/entities.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/font_size.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/fullscreen.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/image.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/image_manager.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/link.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/lists.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/paragraph_format.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/paragraph_style.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/quick_insert.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/save.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/url.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/froala_editor/plugins/video.min.js");
    }
}


if (!function_exists('load_sort_nest')) {

    function load_sort_nest(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/lib/sortable/sortable.css");
        /////////// Plugin
        //        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/shortable-nestable/Sortable.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/sortable/jquery.mjs.nestedSortable.js");
        //        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/shortable-nestable/jquery.nestable.js");
    }
}


if (!function_exists('load_chossen')) {

    function load_chossen(&$data)
    {
        array_push($data['stylesheet_tag'], base_url() . "public/lib/chosen/chosen.min.css");
        /////////// Plugin
        //        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/shortable-nestable/Sortable.min.js");
        array_push($data['javascript_tag'], base_url() . "public/lib/chosen/chosen.jquery.js");
        //        array_push($data['javascript_tag'], base_url() . "public/admin/vendor/shortable-nestable/jquery.nestable.js");
    }
}
