<div class="container">
    <div class="auth-form">
        <h1>Betting System</h1>
        <h2>Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" name="login" id="login" placeholder="Enter login" required>
                <span class="error" id="loginError"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password" required>
                <span class="error" id="passwordError"></span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>

        </form>
        <div class="alert alert-error" id="loginAlert" style="display:none"></div>
        <div class="login-credentials">
            <button class="btn-credentials-toggle" onclick="$('#credTable').toggle(); $(this).text(function(i,t){return t==='Show test credentials'?'Hide test credentials':'Show test credentials'})">Show test credentials</button>
            <div id="credTable" style="display:none">
                <table>
                    <tr><th>Login</th><th>Password</th><th>Role</th></tr>
                    <tr><td>admin</td><td>password</td><td>Admin</td></tr>
                    <tr><td>john_doe</td><td>password</td><td>User</td></tr>
                    <tr><td>jane_smith</td><td>password</td><td>User</td></tr>
                    <tr><td>bob_wilson</td><td>password</td><td>User</td></tr>
                </table>
            </div>
        </div>
        <div class="login-footer">
            <a href="https://github.com/rowlin/saule_test" target="_blank">GitHub</a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $('#loginAlert').hide();

        $.post('/login/login', {
            login: $('#login').val(),
            password: $('#password').val()
        }, function(response) {
            if (response.success) {
                if (response.user.is_admin) {
                    window.location.href = '/admin';
                } else {
                    window.location.href = '/';
                }
            }
        }).fail(function(xhr) {
            var err = xhr.responseJSON ? xhr.responseJSON.error : 'Login failed';
            $('#loginAlert').text(err).show();
        });
    });
});
</script>
