<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success btn-sm" href="{{base_url()}}resulte/add">Thêm</a>
                <div style="margin-left:auto;">
                    <input type="text" id="daterange" class="form-control form-control-sm btn-group" style="width: 200px;" placeholder="Search Time" />
                </div>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Tên NV</th>
                            <th>Khu vực</th>
                            <th>Ngày lấy mẫu</th>
                            <th>Đầu <br><i>Head:</i></th>
                            <th>Mũi <br><i>Noise:</i></th>
                            <th>Ngực <br><i>Chest:</i></th>
                            <th>Cẳng tay trái <br><i>Left forearm:</i></th>
                            <th>Cẳng tay phải <br><i>Right forearm:</i></th>
                            <th>Dấu găng tay trái <br><i>Left glove print 5 fingers:</i></th>
                            <th>Dấu găng tay phải <br><i>Right glove print 5 fingers:</i></th>
                            <th>Hành động</th>
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
                searchPlaceholder: "Mã hoặc tên NV"
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
                    "data": "action"
                }
            ]

        });
    });
</script>