<div class="app-header header-shadow">
    <div class="app-header__logo">
        <div class="logo-src"><img src="{{base_url()}}public/img/logo.png" width="150" /></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic"
                    data-class="closed-sidebar">
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
            <button class="btn btn-primary" id="btn_object_select" tabindex="-1" data-target='#object-modal'
                data-toggle='modal'></button>
        </div>
        <div class="app-header-right">
            <div class="header-dots">
                <div class="dropdown">
                    <button type="button" data-toggle="dropdown" class="p-0 mr-2 btn btn-link" aria-expanded="true">
                        @if(language_current() == "english")
                        <img src="{{base_url()}}public/img/en.png" width="42">
                        @else
                        <img src="{{base_url()}}public/img/vn.png" width="42">
                        @endif
                    </button>
                    <div tabindex="-1" role="menu" aria-hidden="true"
                        class="rm-pointers dropdown-menu dropdown-menu-right" x-placement="bottom-end">
                        <h6 tabindex="-1" class="dropdown-header">{{lang("choose_language")}}</h6>
                        <button type="button" tabindex="0" class="dropdown-item language" data-language="english">
                            <img src="{{base_url()}}public/img/en.png">
                        </button>
                        <button type="button" tabindex="0" class="dropdown-item language" data-language="vietnamese">
                            <img src="{{base_url()}}public/img/vn.png">
                        </button>
                    </div>
                </div>
            </div>
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
                                <div tabindex="-1" role="menu" aria-hidden="true"
                                    class="dropdown-menu dropdown-menu-right">
                                    <a type="button" tabindex="0" class="dropdown-item"
                                        href="{{base_url()}}/admin/account">{{lang("info")}}</a>
                                    <div tabindex="-1" class="dropdown-divider"></div>
                                    <a type="button" tabindex="0" class="dropdown-item"
                                        href="{{base_url()}}/index/logout">{{lang("logout")}}</a>
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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="object-modalLabel">
                    {{lang("choose_object")}}
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
                        <!-- @foreach($objects as $row)
                        <div class="col-md-3 col-4 my-4 text-center">
                            <a class="btn btn-success btn-xs object_select" href="#" data-id="{{$row['id']}}" data-name="{{$row['name']}}">
                                {{$row['name']}}
                            </a>
                        </div>
                        @endforeach -->

                        <div class="col-lg-6">
                            <fieldset class="the-fieldset mb-4">
                                <legend class="h5 text-center p-3">{{lang("vi_sinh")}}</legend>
                                <div class="row no-glutters">
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['3']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="3" data-name="{{ pick_language($object['3'],'name') }}">
                                            {{ pick_language($object['3'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['10']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="10" data-name="{{ pick_language($object['10'],'name') }}">
                                            {{ pick_language($object['10'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['11']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="11" data-name="{{ pick_language($object['11'],'name') }}">
                                            {{ pick_language($object['11'],'name') }}
                                        </a>
                                    </div>

                                </div>
                            </fieldset>
                        </div>
                        <div class="col-lg-6">
                            <fieldset class="the-fieldset mb-4">
                                <legend class="h5 text-center p-3">{{lang("khi")}}</legend>
                                <div class="row no-glutters">
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['16']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="16" data-name="{{ pick_language($object['16'],'name') }}">
                                            {{ pick_language($object['16'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['17']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="17" data-name="{{ pick_language($object['17'],'name') }}">
                                            {{ pick_language($object['17'],'name') }}
                                        </a>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="col-lg-6">
                            <fieldset class="the-fieldset mb-4">
                                <legend class="h5 text-center p-3">{{lang("tieu_phan")}}</legend>
                                <div class="row no-glutters">
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['15']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="15" data-name="{{ pick_language($object['15'],'name') }}">
                                            {{ pick_language($object['15'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['14']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="14" data-name="{{ pick_language($object['14'],'name') }}">
                                            {{ pick_language($object['14'],'name') }}
                                        </a>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="col-lg-6">
                            <fieldset class="the-fieldset mb-4">
                                <legend class="h5 text-center p-3">{{lang("nuoc")}}</legend>
                                <div class="row no-glutters">
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['19']['is_allowed'] != 1 ? 'disabled' : ''}}"
                                            href="#" data-id="19" data-name="{{ pick_language($object['19'],'name') }}">
                                            {{ pick_language($object['19'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['18']['is_allowed'] != 1 ? 'disabled' : ''}}" href="#" data-id="18"
                                            data-name="{{ pick_language($object['18'],'name') }}">
                                            {{ pick_language($object['18'],'name') }}
                                        </a>
                                    </div>
                                    <div class="col text-center">
                                        <a class="btn btn-success btn-xs object_select {{$object['20']['is_allowed'] != 1 ? 'disabled' : ''}}" href="#" data-id="20"
                                            data-name="{{ pick_language($object['20'],'name') }}">
                                            {{ pick_language($object['20'],'name') }}
                                        </a>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>