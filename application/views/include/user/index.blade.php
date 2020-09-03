<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}user/add">{{lang("add")}}</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("login_identity_label")}}</th>
                            <th>{{lang("login_name_label")}}</th>
                            <th>{{lang("index_groups_th")}}</th>
                            <th>{{lang("index_active_link")}}</th>
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
            "language": {
                url: url
            },
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "user/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [{
                    "data": "username"
                },
                {
                    "data": "last_name"
                },
                {
                    "data": "groups"
                },
                {
                    "data": "active"
                },
                {
                    "data": "action"
                },
            ]

        });
    });
</script>