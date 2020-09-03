<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<?= $widget->position_tree_header("position"); ?>
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}position/add">{{lang("add")}}</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("code")}}</th>
                            <th>{{lang("login_name_label")}}</th>
                            <th>{{lang("room")}}/{{lang("equipment")}}</th>
                            <th>{{lang("area")}}/{{lang("system_water")}}</th>
                            <th>{{lang("department")}}</th>
                            <th>{{lang("factory")}}</th>
                            <th>{{lang("report")}}</th>
                            <th>{{lang("index_action_th")}}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?= $widget->position_tree("position"); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "language": {
                url: url
            },
            "ajax": {
                "url": path + "position/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "string_id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "department_name"
                },
                {
                    "data": "area_name"
                },
                {
                    "data": "workshop_name"
                },
                {
                    "data": "factory_name"
                },
                {
                    "data": "type_bc"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>