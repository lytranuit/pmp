<div class="app-header header-shadow">
    <div class="app-header__logo">
        <div class="logo-src"><img src="{{base_url()}}public/img/logo.png" width="150" /></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="app-header__content">
        <div class="app-header-left">
            <button class="btn btn-primary" id="btn_object_select" tabindex="-1" data-target='#object-modal' data-toggle='modal'>Vi Sinh</button>
        </div>
        <div class="app-header-right">
            <div class="header-btn-lg pr-0">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">

                        <div class="widget-content-left  ml-3 header-user-info">
                            <div class="widget-heading">
                                {{$userdata['name']}}
                            </div>
                            <div class="widget-subheading">

                            </div>
                        </div>
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                    <a type="button" tabindex="0" class="dropdown-item" href="{{base_url()}}/admin/account">User Account</a>
                                    <div tabindex="-1" class="dropdown-divider"></div>
                                    <a type="button" tabindex="0" class="dropdown-item" href="{{base_url()}}/index/logout">Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-labelledby="object-modalLabel" class="modal fade" id="object-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="object-modalLabel">
                    Select Object
                </h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        x
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        @foreach($objects as $row)
                        <div class="col-md-3 col-4 my-4">
                            <a class="btn btn-success btn-xs object_select" href="#" data-id="{{$row['id']}}" data-name="{{$row['name']}}">
                                {{$row['name']}}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>