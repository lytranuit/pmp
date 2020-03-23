
<div class="row clearfix">
    <div class="col-12">
        <div class="section-block">
            <h3 class="section-title">Lịch sử thu/chi</h3>
        </div>
        <div class="card card-fluid">
            <div class="card-header">
                <a href='#' class="btn btn-sm btn-success" id="add_paid" data-target="#paid-modal" data-toggle="modal">Thêm</a>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày</th>
                                <th>Số tiền</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" aria-labelledby="form-modalLabel" class="modal fade" id="paid-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="comment-modalLabel">
                    Thêm thu/chi
                </h4>
            </div>
            <div class="modal-body">
                <div class="main">
                    <!--<p>Sign up once and watch any of our free demos.</p>-->
                    <form id="form-modal" action="{{base_url()}}admin/savepaid" method="POST">

                        <input type="hidden" name="id" value="0"/>
                        <div class="form-group">
                            <b class="form-label">Số tiền</b>
                            <div class="form-line">
                                <input type="text" class="form-control" name="amount" id='amount'required="">
                            </div>
                        </div>
                        <div class="form-group">
                            <b  class="form-label">Ngày:<i class="text-danger">*</i></b>
                            <div class="form-line">
                                <input class="form-control" type='date' name="date" required="" value="{{date("Y-m-d")}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <b  class="form-label">Ghi chú:</b>
                            <div class="form-line">
                                <textarea rows="4" class="form-control" name="note"></textarea>
                            </div>
                        </div>
                        <button class="btn btn-primary waves-effect" type="submit" name="cap_nhat">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function () {

        $('#quanlytin').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "admin/tabledebt",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [
                {"data": "id"},
                {"data": "date"},
                {"data": "amount"},
                {"data": "note"},
                {"data": "action"},
            ]

        });
        $('#amount').inputmask("numeric", {
            radixPoint: ".",
            groupSeparator: ",",
            autoGroup: true,
            suffix: ' VND', //No Space, this will truncate the first character
            rightAlign: false,
            oncleared: function () {
                self.Value('');
            }
        });
        $.validator.setDefaults({
            debug: true,
            success: "valid"
        });
        $("#form-modal").validate({
            highlight: function (input) {
                $(input).parents('.form-line').addClass('error');
            },
            unhighlight: function (input) {
                $(input).parents('.form-line').removeClass('error');
            },
            errorPlacement: function (error, element) {
                $(element).parents('.form-group').append(error);
            },
            submitHandler: function (form) {
                form.submit();
                return false;
            }
        });
        $('#add_paid').on('click', function (e) {
            var date = moment().format("YYYY-MM-DD");
            fillForm($("#form-modal"), {id: 0, date: date, amount: '', note: ''});
        });

        $(document).off("click", '.edit_paid').on('click', '.edit_paid', function (e) {
            e.preventDefault();
            let tr = $(this).parents("tr");
            let id = $(this).data("id");
            var date = $("td:eq(1)", tr).text();
            var amount = $("td:eq(2)", tr).text();
            var note = $("td:eq(3)", tr).text();
            var paid_obj = {
                amount: amount,
                note: note,
                date: date,
                id: id
            }
            console.log(paid_obj);
            fillForm($("#form-modal"), paid_obj);
        });
    });
</script>