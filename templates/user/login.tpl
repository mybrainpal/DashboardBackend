<!-- Login JS -->
<script src="{:PATH:}/frontend/js/actions/user/login.js"></script>

<!-- Sign In HTML Form -->
<div class = "vertical-center fade-in slide-down" id = "divUserLoginMain">
	<img src = "{:PATH:}/frontend/images/Logo - Shield.png" class = "img-responsive center-block" width = "25%">
	<form id = "divUserLoginForm" style = "width: 15%;" class="center-block" onSubmit="return login();">
		<div class = "form-group">
            <input class = "form-control" type = "text" name = 'username' placeholder = "Username"/>          
        </div>
        <div class = "form-group">
            <input class = "form-control" type = "password" name = 'password' placeholder = "Password"/>     
        </div>
		<button class = "btn btn-lg btn-primary btn-block" type = "submit">Login</button>
		<div style = "margin-top: 10px;" class = "text-center form-group has-error">
			<label id = "labelUserLoginError" class="control-label" for="inputError"></label>
		</div>
	</form>
</div>