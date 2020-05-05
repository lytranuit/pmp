<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}limit/add">Add</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Effective date</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Acceptance criteria</th>
                            <th>Alert Limit</th>
                            <th>Action Limit</th>
                            <th>Status</th>
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
            "ajax": {
                "url": path + "limit/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "day_effect"
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