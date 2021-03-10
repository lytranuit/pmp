<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}import/add">{{lang("add")}}</a>
                
                <div style="margin-left:auto;">
                    <a href="{{base_url()}}import/import_all" id="import_all" class="btn btn-primary"
                        data-type="confirm" title="{{lang("import_all")}}"><i
                            class="fas fa-file-import mr-1"></i>{{lang("import_all")}}</a>
                </div>
            </h5>

            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0"
                    width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>{{lang("comment")}}</th>
                            <th>{{lang("date")}}</th>
                            <th>{{lang("user")}}</th>
                            <th>{{lang("file")}}</th>
                            <th>{{lang("logs")}}</th>
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
        //$('#daterange').daterangepicker({
        //    maxDate: moment(),
        //    autoUpdateInput: false,
        //    locale: {
        //        cancelLabel: 'Clear'
        //    }

        //}, function(start, end, label) {
        //    // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        //});
        //$('#daterange').on('apply.daterangepicker', function(ev, picker) {
        //    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        //    $('#quanlytin').DataTable().ajax.reload();

        //});
        $('#quanlytin').DataTable({
            "language": {
                "url": url
            },
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "import/table",
                "dataType": "json",
                "type": "POST",
                "data": function(d) {
                    d.daterange = $('#daterange').val();
                }
            },
            "columns": [{
                    "data": "id"
                },{
                    "data": "note"
                },
                {
                    "data": "date"
                }, {
                    "data": "user_name"
                },
                {
                    "data": "file"
                },
                {
                    "data": "logs"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>