<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <!--                <a class="btn btn-success btn-sm" href="{{base_url()}}result/add">Thêm</a>-->
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("date")}}</th>
                            <th>{{lang("user")}}</th>
                            <th>{{lang("description")}}</th>
                            <th>{{lang("old_values")}}</th>
                            <th>{{lang("new_values")}}</th>
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
                "url": url
            },
            "ajax": {
                "url": path + "history/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "created_at"
                }, {
                    "data": "name"
                },
                {
                    "data": "description"
                },
                {
                    "data": "old_values"
                },
                {
                    "data": "new_values"
                }
            ]

        });
    });
</script>