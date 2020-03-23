
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">Page</h2>
            <p class="pageheader-text"></p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Page</li>
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
                <a class="btn btn-success btn-sm" href="{{base_url()}}admin/thempage">Thêm page</a>
            </h5>
            <div class="card-body">
                <table id="quanlytin" class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Tiêu đề</th>
                            <th>Nội dung</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($arr_tin as $key=>$tin)
                        <tr>
                            <td>{{$tin->id}}</td>
                            <td>{{$tin->title}}</td>
                            <td><?= split_string($tin->content, 50) ?></td>
                            <td>
                                <a href="{{base_url()}}admin/editpage/{{$tin->id}}" class="btn btn-warning btn-xs" title="edit">
                                    <i class="fas fa-pencil-alt">
                                    </i>
                                </a>
                                <a href="{{base_url()}}admin/removepage/{{$tin->id}}" class="btn btn-danger btn-xs" title="Remove it?" data-type='confirm'>
                                    <i class="far fa-trash-alt">
                                    </i>
                                </a>
                            </td>
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
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": path + "object/table",
                "dataType": "json",
                "type": "POST",
            },
            "columns": [
                {"data": "name"},
                {"data": "action"}
            ]

        });
    });
</script>