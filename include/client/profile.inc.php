<h1 class="text-center"><?php echo __('Manage Your Profile Information'); ?></h1>
<p class="text-center"><?php echo __(
'Use the forms below to update the information we have on file for your account.'
); ?>
</p>

<hr>

<form action="profile.php" method="post">
	<?php csrf_token(); ?>

	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<?php
			foreach ($user->getForms() as $f) {
			    $f->render(false);
			} ?>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->

<?php if ($acct = $thisclient->getAccount()) {
    $info=$acct->getInfo();
    $info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
			
	
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<h3><?php echo __('Preferences'); ?></h3>
			
			<div class="form-group">
				<label><?php echo __('Time Zone'); ?></label>
				<select name="timezone_id" id="timezone_id">
		            <option value="0" class="form-control">&mdash; <?php echo __('Select Time Zone'); ?> &mdash;</option>
		            <?php
		            $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
		            if(($res=db_query($sql)) && db_num_rows($res)){
		                while(list($id,$offset, $tz)=db_fetch_row($res)){
		                    $sel=($info['timezone_id']==$id)?'selected="selected"':'';
		                    echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
		                }
		            }
		            ?>
		        </select>
		        <span class="error"><?php echo $errors['timezone_id']; ?></span>
			</div>
			<!-- /.form-group -->
			
			<div class="form-group">
				<label class="checkbox-inline">
					<input type="checkbox" name="dst" value="1" <?php echo $info['dst']?'checked="checked"':''; ?> class="form-control"><?php echo __('Observe daylight saving'); ?> <em><small>(<?php echo __('Current Time'); ?>: <?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['dst']); ?>)</small></em>
		        </label>
			</div>
			<!-- /.form-group -->
			
			<div class="form-group">
				<label><?php echo __('Preferred Language'); ?></label>
				<?php
				$langs = Internationalization::availableLanguages(); ?>
	            <select name="lang" class="form-control">
	                <option value="">&mdash; <?php echo __('Use Browser Preference'); ?> &mdash;</option>
					<?php foreach($langs as $l) {
						$selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
						<option value="<?php echo $l['code']; ?>" <?php echo $selected;
	                    ?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
					<?php } ?>
	            </select>
	            <span class="error"><?php echo $errors['lang']; ?></span>
			</div>
			<!-- /.form-group -->
			
			<div class="form-group">
				<label><?php echo __('Preferred Language'); ?></label>
				<?php
				$langs = Internationalization::availableLanguages(); ?>
	            <select name="lang" class="form-control">
	                <option value="">&mdash; <?php echo __('Use Browser Preference'); ?> &mdash;</option>
					<?php foreach($langs as $l) {
						$selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
						<option value="<?php echo $l['code']; ?>" <?php echo $selected;
	                    ?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
					<?php } ?>
	            </select>
	            <span class="error"><?php echo $errors['lang']; ?></span>
			</div>
			<!-- /.form-group -->
			
			<?php if ($acct->isPasswdResetEnabled()) { ?>
			
				<h3><?php echo __('Access Credentials'); ?></h3>
				
				<?php if (!isset($_SESSION['_client']['reset-token'])) { ?>
			
					<div class="form-group">
						<label><?php echo __('Current Password'); ?></label>
						<input type="password" name="cpasswd" value="<?php echo $info['cpasswd']; ?>" class="form-control">
						<span class="error"><?php echo $errors['cpasswd']; ?></span>
					</div>
					<!-- /.form-group -->
					
				<?php } ?>
					
					<div class="form-group">
						<label><?php echo __('New Password'); ?></label>
						<input type="password" name="passwd1" value="<?php echo $info['passwd1']; ?>" class="form-control">
						<span class="error"><?php echo $errors['passwd1']; ?></span>
					</div>
					<!-- /.form-group -->
					
					<div class="form-group">
						<label><?php echo __('Confirm New Password'); ?></label>
						<input type="password" name="passwd2" value="<?php echo $info['passwd2']; ?>" class="form-control">
						<span class="error"><?php echo $errors['passwd2']; ?></span>
					</div>
					<!-- /.form-group -->
			
			<?php } ?>
			<?php } ?>
			
			<div class="form-group">
				<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Update</button>
				<button type="reset" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</button>
				<button type="button" onclick="javascript: window.location.href='index.php';" class="btn btn-default"><i class="fa fa-times"></i>  Cancel</button>
			</div>
			<!-- /.form-group -->
			
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->

	
</form>
