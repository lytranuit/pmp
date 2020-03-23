
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">Ngôn ngữ</h2>
            <p class="pageheader-text"></p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ngôn ngữ</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-success" id='Save' href="#">Save</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Tiếng Việt</th>
                            <th>Tiếng Anh</th>
                            <th>Tiếng Nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moduleData as $key=>$row)
                        <tr>
                            <td class="key">{{$key}}</td>
                            <td><input type='text' style="width:100%;" class="form-control vietnamese" value='{{$row['vietnamese'] or ""}}' /></td>
                            <td><input type='text' style="width:100%;" class="form-control english" value='{{$row['english'] or ""}}' /></td>
                            <td><input type='text' style="width:100%;" class="form-control japanese" value='{{$row['japanese'] or ""}}' /></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#quanlytin').DataTable({
            "lengthMenu": [[-1], ["All"]]
        });
        $("#Save").click(function (e) {
            e.preventDefault();
            var data = {vietnamese: {}, english: {}, japanese: {}};
            $("#quanlytin tbody tr").each(function () {
                var key = $(".key", $(this)).text();
                var vietnamese = $(".vietnamese", $(this)).val();
                var english = $(".english", $(this)).val();
                var japanese = $(".japanese", $(this)).val();
                data['vietnamese'][key] = vietnamese;
                data['english'][key] = english;
                data['japanese'][key] = japanese;
            });
//            console.log(data);
//           return false;
            $.ajax({
                url: path + "admin/savelanguage",
                type: "POST",
                dataType: "JSON",
                data: {data: JSON.stringify(data)},
                success: function (res) {
                    location.reload();
                }
            })
        })
    }
    );
</script>