<div class="row">
    <div class="col-12">
        <div class="card" id="doanhthu">
            <div class="card-header">
                <h5>Biểu đồ xu hướng </h5>
                <div class="btn-group btn-group-toggle" style="margin-left:auto" data-toggle="buttons">
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option3" value="Week"> Tuần
                    </label>
                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Tháng
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Quý
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option4" value="Month"> Nửa năm
                    </label>

                    <label class="btn btn-light">
                        <input type="radio" name="options" id="option5" value="Year"> Năm
                    </label>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenue" width="400" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {});
</script>