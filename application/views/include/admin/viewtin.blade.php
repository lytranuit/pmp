<ol class="breadcrumb breadcrumb-bg-grey">
    <li><a href="javascript:void(0);">Home</a></li>
    <li class="active"><a href="javascript:void(0);">Thông tin nội bộ</a></li>
</ol>
<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>{{$tin['title_vi']}}</h2>
            </div>
            <div class="body">
                <div class="fr-view">
                    <?= $tin['content_vi'] ?>
                </div>
                @if(count($tin['files']))
                <p>
                    <b>File đính kèm:</b>
                <p>
                <div>
                    @foreach($tin['files'] as $row)
                    <a href="{{base_url(). 'ajax/downloadfile?id='.$row->id_hinhanh}}" class="files d-block mt-1">
                        <i class="file-icon" data-type="<?= pathinfo($row->real_hinhanh, PATHINFO_EXTENSION); ?>"></i>
                        {{$row->real_hinhanh}}
                    </a>
                    <br>
                    <br>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>