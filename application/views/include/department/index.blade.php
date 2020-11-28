<?= $widget->position_tree_header("department"); ?>
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}department/add" data-target="#form-modal"
                    data-toggle="modal">{{lang("add")}}</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0"
                    width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("code")}}</th>
                            <th>{{lang("login_name_label")}}</th>
                            <th>{{lang("area")}}</th>
                            <th>{{lang("department")}}</th>
                            <th>{{lang("factory")}}</th>
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

<script type="text/javascript">
    $(document).ready(function() {
        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "language": {
                url: url
            },
            "ajax": {
                "url": path + "department/table",
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
                    "data": "area_name"
                },
                {
                    "data": "workshop_name"
                },
                {
                    "data": "factory_name"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>