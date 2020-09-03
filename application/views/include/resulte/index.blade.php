<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}resulte/add">{{lang("add")}}</a>
                <div style="margin-left:auto;">
                    <input type="text" id="daterange" class="form-control form-control-sm btn-group" style="width: 200px;" placeholder="{{lang('search_time')}}" />
                </div>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{lang("employee_code")}}</th>
                            <th>{{lang("employee_name")}}</th>
                            <th>{{lang("area")}}</th>
                            <th>{{lang("date")}}</th>
                            <th class="text-center">{{lang("head")}}</th>
                            <th class="text-center">{{lang("nose")}}</th>
                            <th class="text-center">{{lang("chest")}}</th>
                            <th class="text-center">{{lang("left_forearm")}}</th>
                            <th class="text-center">{{lang("right_forearm")}}</th>
                            <th class="text-center">{{lang("left_glove")}}</th>
                            <th class="text-center">{{lang("right_glove")}}</th>
                            <th>{{lang("comment")}}</th>
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
        $('#daterange').daterangepicker({
            maxDate: moment(),
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }

        }, function(start, end, label) {
            // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            $('#quanlytin').DataTable().ajax.reload();

        });
        $('#quanlytin').DataTable({
            "language": {
                "url": url
            },
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "resulte/table",
                "dataType": "json",
                "type": "POST",
                "data": function(d) {
                    d.daterange = $('#daterange').val();
                }
            },
            "columns": [{
                    "data": "employee_string_id"
                },
                {
                    "data": "employee_name"
                },
                {
                    "data": "area_name"
                },
                {
                    "data": "date"
                },
                {
                    "data": "value_H"
                },
                {
                    "data": "value_N"
                },
                {
                    "data": "value_C"
                },
                {
                    "data": "value_LF"
                },
                {
                    "data": "value_RF"
                },
                {
                    "data": "value_LG"
                },
                {
                    "data": "value_RG"
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