<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}limit/add">{{lang("add")}}</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("effective_date")}}</th>
                            <th>{{lang("factory")}}</th>
                            <th>{{lang("department")}}</th>
                            @if($object_id <= 17) <th>{{lang("area")}}</th>
                                @else
                                <th>{{lang("system_water")}}</th>
                                @endif
                                <th>{{lang("method")}}</th>
                                <th>{{lang("acceptance_criteria")}}</th>
                                <th>{{lang("alert_limit")}}</th>
                                <th>{{lang("action_limit")}}</th>
                                <th>{{lang("status")}}</th>
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
                "url": path + "limit/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "day_effect"
                },
                {
                    "data": "factory_name"
                },
                {
                    "data": "workshop_name"
                },
                {
                    "data": "area_name"
                },
                {
                    "data": "target_name"
                },
                {
                    "data": "standard_limit"
                },
                {
                    "data": "alert_limit"
                },
                {
                    "data": "action_limit"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>