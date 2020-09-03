<div class="card card-fluid">
    <div class="card-header drag-handle">
        {{lang("info")}}
    </div>
    <div class="card-body">
        <form id="form_advanced_validation" method="POST" novalidate="novalidate">
            <div class="form-group">
                <b class="form-label">{{lang("login_identity_label")}}:</b>
                <div class="form-line">
                    <input type="text" class="form-control" value="{{$user->username}}" name="username" required="" aria-required="true" readonly="">
                </div>
                <div class="help-info"></div>
            </div>
            <div class="form-group">
                <b class="form-label">{{lang("login_name_label")}}</b>
                <div class="form-line">
                    <input type="text" class="form-control" value="{{$user->last_name}}" name="last_name" minlength="3" required="" aria-required="true">
                </div>
            </div>

            <div class="form-group">
                <b class="form-label">{{lang("login_phone_label")}}</b>
                <div class="form-line">
                    <input type="text" class="form-control" value="{{$user->phone}}" name="phone" minlength="3" required="" aria-required="true">
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <a href="#" data-target="#password-modal" data-toggle="modal">
                    <i class="fas fa-lock" style='font-size: 20px;vertical-align: middle;'></i>
                    <span>{{lang("thay_doi_password")}}</span>
                </a>
            </div>
            <button class="btn btn-primary waves-effect" type="submit" name="edit_user">{{lang("change_password_submit_btn")}}</button>
        </form>
    </div>
</div>
<!-- THAY DOI MAT KHAU Modal-->
<div aria-hidden="true" aria-labelledby="password-modalLabel" class="modal fade" id="password-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="comment-modalLabel">
                    {{lang("thay_doi_password")}}
                </div>
            </div>
            <div class="modal-body">
                <div class="main">
                    <!--<p>Sign up once and watch any of our free demos.</p>-->
                    <form id="form-password">
                        <div class="form-group">
                            <b class="form-label">{{lang("change_password_old_password_label")}}:</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="password" required="" aria-required="true">
                            </div>
                            <div class="help-info"></div>
                        </div>
                        <div class="form-group">
                            <b class="form-label">{{lang("change_password_new_password_label")}}:</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="newpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <b class="form-label">{{lang("change_password_new_password_confirm_label")}}:</b>
                            <div class="form-line">
                                <input type="password" class="form-control" name="confirmpassword" minlength="6" required="" aria-required="true">
                            </div>
                        </div>
                        <button class="btn btn-primary waves-effect" type="submit" name="edit_password">{{lang("change_password_submit_btn")}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $(".modal").detach().appendTo("body");
        $("button[name='edit_password']").click(function(e) {
            e.preventDefault();
            $.ajax({
                url: path + "admin/changepass",
                data: $("#form-password").serialize(),
                dataType: "JSON",
                type: "POST",
                success: function(data) {
                    alert(data.msg);
                    if (data.code == 400) {
                        location.reload();
                    }
                }
            });
            return false;
        });
    })
</script>