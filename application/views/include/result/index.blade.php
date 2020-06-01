<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}result/add">Add</a>
                <div style="margin-left:auto;">
                    <input type="text" id="daterange" class="form-control form-control-sm btn-group" style="width: 200px;" placeholder="Search Time" />
                </div>
            </h5>

            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>
                                @if($object_id == 11 || $object_id == 15)
                                Room
                                @else
                                Equipment
                                @endif
                            </th>
                            <th>Method</th>
                            <th>Code</th>
                            <th>Frequency</th>
                            <th>Date</th>
                            <th class="text-center">Value</th>
                            <th>Comment</th>
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
        $('#daterange').daterangepicker({
            maxDate: moment(),
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }

        }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            $('#quanlytin').DataTable().ajax.reload();

        });
        $('#quanlytin').DataTable({
            language: {
                searchPlaceholder: "Mã vị trí"
            },
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "result/table",
                "dataType": "json",
                "type": "POST",
                "data": function(d) {
                    d.daterange = $('#daterange').val();
                }
            },
            "columns": [{
                    "data": "department_name"
                },
                {
                    "data": "target_name"
                },
                {
                    "data": "position_string_id"
                },
                {
                    "data": "frequency_name"
                },
                {
                    "data": "date"
                },
                {
                    "data": "value"
                },
                {
                    "data": "note"
                },
                {
                    "data": "action"
                }
            ]

        });
    });
</script>