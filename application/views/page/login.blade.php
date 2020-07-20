<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{$title}}</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>public/lib/bootstrap/css/bootstrap.min.css">
    <style>
        html,
        body {
            height: 100%;
            width: 100%;
            font-family: 'Circular Std Book';
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            color: #71748d;
            background-color: #efeff6;
            -webkit-font-smoothing: antialiased;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            font-size: 14px;
        }

        .splash-container {
            width: 100%;
            max-width: 500px;
            padding: 15px;
            margin: auto;
        }

        .splash-container .card-header {
            padding: 20px;
        }

        .splash-description {
            text-align: center;
            display: block;
            line-height: 20px;
            font-size: 1rem;
            margin-top: 5px;
            padding-bottom: 10px;
        }

        .splash-title {
            text-align: center;
            display: block;
            font-size: 14px;
            font-weight: 300;
        }

        .splash-container .card-footer-item {
            padding: 12px 28px;
        }
    </style>
</head>

<body>
    <!-- ============================================================== -->
    <!-- login page  -->
    <!-- ============================================================== -->
    <div class="splash-container">
        <div class="card">
            <div class="card-header text-center"><span>{{lang('login')}}</span></div>
            <div class="card-body">
                @if($message != '')
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <form id="form-login" method="POST">
                    <div class="form-group">
                        <input class="form-control" id="username" name="identity" type="text" placeholder="<?= lang('login_identity_label') ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input class="form-control" id="password" name="password" type="password" placeholder="<?= lang('login_password_label') ?>">
                    </div>
                    <div class="form-group">
                        <label class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" value="1" checked="" name="remember"><span class="custom-control-label">{{lang('remember_me')}}</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="button_login">{{lang('login')}}</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>