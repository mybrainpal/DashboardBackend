<!-- User Creation JS -->
<script src="{:PATH:}/frontend/js/actions/users/users.js"></script>
<script src="{:PATH:}/frontend/js/actions/users/create.js"></script>

<!-- Modal Register -->
<form role="form">
	<div style = 'margin-bottom: 20px; border-bottom: 1px solid white;'>
		<h4><span style='color: var(--color-text);'>Basic Information:</span></h4>
	</div>

	<div class="form-group">
        <input type="text" class="form-control form-vaultra form-vaultra-mandatory" name="username" placeholder="Username">
    </div>
    <div class="form-group">
        <input type="password" class="form-control form-vaultra form-vaultra-mandatory" name="password" placeholder="Password">
    </div>
    
    <div style = 'margin-top:40px; margin-bottom: 20px; border-bottom: 1px solid white;'>
		<h4><span style='color: var(--color-text);'>Additional Information:</span></h4>
	</div>
	
    <div class="form-group">
        <input type="text" class="form-control form-vaultra" id="r_input_fname" placeholder="First Name">
    </div>
    <div class="form-group">
        <input type="text" class="form-control form-vaultra" id="r_input_lname" placeholder="Last Name">
    </div>
    <div class="form-group">
        <input type="email" class="form-control form-vaultra" id="r_input_email" placeholder="Email address">
    </div>
    <div class="form-group">
        <select class="form-control form-vaultra" title="Gander" id="r_input_gander">
            <option>Male</option>
            <option>Female</option>
        </select>
    </div>
    <div class="form-group">
        <input type="date" class="form-control form-vaultra" id="r_input_birthday" placeholder="Birthday">
    </div>
    <div class="form-group">
        <input type="tel" class="form-control form-vaultra" id="r_input_tel" placeholder="Phone number">
    </div>
</form>
<button type="submit" class="btn btn-vaultra" data-dismiss="modal" id='register_submit'>Create</button>