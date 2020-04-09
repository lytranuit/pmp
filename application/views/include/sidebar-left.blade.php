<!--<div class="nav-left-sidebar sidebar-dark">
    <div class="menu-list">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="d-xl-none d-lg-none" href="#">Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-divider">
                        Tổng quan
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{base_url()}}admin/">Tổng quan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{base_url()}}admin/quanlypage">Page</a>
                    </li>
                    @if($is_admin)
                    <li class="nav-item">
                        <a class="nav-link" href="{{base_url()}}admin/quanlyuser">User</a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" href="{{base_url()}}admin/settings">Cài đặt chung</a>
                    </li>
                    <li style="height: 100px;">

                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>-->

<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div><img src="{{base_url()}}public/img/logo.png" width="150" /></div>
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
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading">Tổng quan</li>
                <li>
                    <a href="{{base_url()}}dashboard/" class="">
                        <i class="metismenu-icon far fa-chart-bar"></i>
                        Biểu đồ
                    </a>
                </li>
                <li>
                    <a href="{{base_url()}}report/" class="">
                        <i class="metismenu-icon fas fa-file-word"></i>
                        Báo cáo
                    </a>
                </li>
                <li class="app-sidebar__heading">Nhập dữ liệu</li>
                <li>
                    <a href="{{base_url()}}result/" class="">
                        <i class="metismenu-icon fa fa-database"></i>
                        Dữ liệu
                    </a>
                </li>
                <li>
                    <a href="{{base_url()}}limit/" class="">
                        <i class="metismenu-icon fas fa-bell"></i>
                        Giới hạn
                    </a>
                </li>
                <li class="app-sidebar__heading">Cài đặt</li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon fa fa-lock"></i>
                        Phân quyền
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{base_url()}}admin/quanlyuser">
                                <i class="metismenu-icon"></i>
                                Tài khoản
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}group/">
                                <i class="metismenu-icon"></i>
                                Nhóm
                            </a>
                        </li>
                    </ul>

                </li>
                <li>
                    <a href="#">
                        <i class="metismenu-icon fas fa-columns"></i>
                        Trường dữ liệu
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{base_url()}}object/">
                                <i class="metismenu-icon"></i>
                                Đối tượng
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}target/">
                                <i class="metismenu-icon"></i>
                                Phương pháp/Chỉ tiêu
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}factory/">
                                <i class="metismenu-icon"></i>
                                Nhà máy
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}workshop/">
                                <i class="metismenu-icon"></i>
                                Xưởng
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}area/">
                                <i class="metismenu-icon"></i>
                                Khu vực
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}department/">
                                <i class="metismenu-icon"></i>
                                Phòng/Thiết bị/Nhân viên
                            </a>
                        </li>
                        <li>
                            <a href="{{base_url()}}position/">
                                <i class="metismenu-icon"></i>
                                Vị trí
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>