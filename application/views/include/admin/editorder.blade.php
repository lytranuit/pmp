<?php
$hinh_preview = isset($tin->hinhanh->src) ? $tin->hinhanh->src : "public/admin/images/avatar-1.jpg";
?>

<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="user_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    Sửa Đơn hàng
                    <button type="submit" name="dangtin" class="btn btn-sm btn-primary float-right">Save</button>
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Mã đơn hàng:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="id" readonly="" required="" disabled=""/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Tên khách hàng:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="customer_name" required="" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Số điện thoại:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="customer_phone"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Email khách hàng:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="customer_email" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Địa chỉ giao hàng:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <input class="form-control" type='text' name="customer_address" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Notes:</b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <textarea class="form-control" name="notes">
                                    </textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <b class="col-12 col-sm-3 col-form-label text-sm-right">Status:<i class="text-danger">*</i></b>
                                <div class="col-12 col-sm-8 col-lg-6 pt-1">
                                    <select class="form-control" name="status">
                                        <option value="1">Mới đặt hàng</option>
                                        <option value="2">Đã xác nhận</option>
                                        <option value="3">Đang vận chuyển</option>
                                        <option value="4">Đã thanh toán</option>
                                        <option value="5">Ghi nợ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
        <div class="row">
            <div class="col-12">
                <div class="section-block">
                    <h3 class="section-title">Sản phẩm</h3>
                </div>
                <div class="card card-fluid">
                    <div class="card-body">
                        <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tin->details as $row)
                                <tr data-id='{{$row->id}}' data-price='{{$row->price}}'>
                                    <td><img src='{{base_url()}}{{$row->image_url or ''}}' width="100"/></td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->code}}</td>
                                    <td>{{number_format($row->price,0,",",".")}}</td>
                                    <td>{{$row->quantity}}</td>
                                    <td><span class="amount">{{number_format($row->amount,0,",",".")}}</span> đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <h3 class="float-right">Tổng đơn hàng: <span class="text-danger total_amount">{{number_format($tin->total_amount,0,",",".")}} đ</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function () {
        var tin = <?= json_encode($tin) ?>;
        fillForm($("#form-dang-tin"), tin);
        $.validator.setDefaults({
            debug: true,
            success: "valid"
        });
        $("#form-dang-tin").validate({
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
        $(".input-qty").change(function () {
//            consoe.log(1);
            var tbody = $(this).parents("tbody");
            var total_amount = 0;
            $("tr", tbody).each(function () {
                var price = $(this).data("price");
                var qty = $(".input-qty", $(this)).val();
                var amount = price * qty;
                total_amount += amount;
                $(".amount", $(this)).text(number_currency(amount, 0));
            });
            $(".total_amount").text(number_currency(total_amount, 0));
        });
        number_currency = function (price, x) {
//            price += ".0";
//            console.log(price);
            var re = '\\d(?=(\\d{' + (x || 3) + '})+' + '$)';
            return price.toFixed(0).replace(new RegExp(re, 'g'), '$&.');
        };
    });
</script>