<?php
if ( !defined( 'OSTCLIENTINC' ) )
	die( 'Access Denied!' );
$info = array();
if ( $thisclient && $thisclient->isValid() ) {
	$info = array( 'name' => $thisclient->getName(),
		'email' => $thisclient->getEmail(),
		'phone' => $thisclient->getPhoneNumber() );
}

$info = ($_POST && $errors) ? Format::htmlchars( $_POST ) : $info;

$form = null;
if ( !$info['topicId'] )
	$info['topicId'] = $cfg->getDefaultTopicId();

if ( $info['topicId'] && ($topic = Topic::lookup( $info['topicId'] )) ) {
	$form = $topic->getForm();
	if ( $_POST && $form ) {
		$form = $form->instanciate();
		$form->isValidForClient();
	}
}
?>

<div class="row"> 

    <div class="col-xs-12"> 
        <header class="page-title text-center">   
            <h1><?php echo __( 'Open a New Ticket' ); ?></h1>
			<p><?php echo __( 'Please fill in the form below to open a new ticket.' ); ?></p>
        </header>
    </div>
    <!-- /.col -->

</div>
<!-- /.row -->

<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
	<div class="row">
		<div class="col-sm-6 col-sm-offset-3 text-center">
			<?php csrf_token(); ?>
			<input type="hidden" name="a" value="open">

			<div class="form-group">
				<label class="required" for="topicId"><?php echo __( 'Help Topic' ); ?><sup class="error">*</sup></label>
				<select id="topicId" name="topicId" onchange="javascript:
                                var data = $(':input[name]', '#dynamic-form').serialize();
                        $.ajax(
                                'ajax.php/form/help-topic/' + this.value,
                                {
                                    data: data,
                                    dataType: 'json',
                                    success: function (json) {
                                        $('#dynamic-form').empty().append(json.html);
                                        $(document.head).append(json.media);
                                    }
                                });" class="form-control">
					<option value="" selected="selected">&mdash; <?php echo __( 'Select a Help Topic' ); ?> &mdash;</option>
					<?php
					if ( $topics = Topic::getPublicHelpTopics() ) {
						foreach ( $topics as $id => $name ) {
							echo sprintf( '<option value="%d" %s>%s</option>', $id, ($info['topicId'] == $id) ? 'selected="selected"' : '', $name );
						}
					} else {
						?>
						<option value="0" ><?php echo __( 'General Inquiry' ); ?></option>
					<?php }
					?>
				</select>
				<span class="error"><?php echo $errors['topicId']; ?></span>
			</div>
			<!-- /.form-group -->
		</div>
		<!-- /.col -->		
	</div>
	<!-- /.row -->
	<hr>
	<?php /*
	<div class="row text-center">
		
		<div class="col-md-8 col-md-offset-2">
			<h5>Se quiser manter-se no anonimato, <u><a href="http://ouvidoria.take01.com.br/pages/porque-exigimos-uma-conta-de-e-mail-valido" target="_blank">você deve</a></u></h5>
			<p><a href="https://www.guerrillamail.com/pt/" target="_blank" class="btn btn-danger btn-lg btn-block text-uppercase">Criar um e-mail anônimo</a></p>
			<p class="bg-success text-left" style="padding:10px;">
				<small>
				É gratuito, rápido, fácil, descartável, seguro, temporário e não precisa de cadastro.
				<br/> 
				Acessando o link acima, um endereço de e-mail aleatório é gerado e você já estará na caixa de entrada.
				<br/> 
				Antes de copiar o email para a área de transferência, verifique se a opção <b>ENDEREÇO ALIAS</b> está marcado. 
				Esta opção irá criptgrafar o e-mail gerado, dando ainda maior segurança.
				<br/> 
				Em seguida, <b>copie o e-mail para a área de transferência e cole no formulário abaixo</b>.
				<br/> 
				Cada e-mail recebido será automaticamente destruído depois de 60 minutos.
				</small>
			</p>
			<p class="bg-warning" style="padding:10px;">
				<small>
					Se o link acima não estiver funcionando, <u><a href="http://ouvidoria.take01.com.br/pages/opcoes-de-email-temporarios" target="_blank" class="btn btn-warning btn-small">experimente outras opções</a></u>
				</small>
			</p>
		</div>
		
	</div>	
	<hr>
	*/ ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<?php
			if (!$thisclient) {
				$uform = UserForm::getUserForm()->getForm( $_POST );
				if ( $_POST )
					$uform->isValid();
				
				$getField_email = $uform ->getField('email')->getFormName();
				
					
				$uform->render( false );
			}
			else {
				?>
				<h3 style="margin-bottom:10px;"> User details </h3>
				<table class="table">
					<tr><td><b><?php echo __( 'Email' ); ?>:</b></td><td><?php echo $thisclient->getEmail(); ?></td></tr>
					<tr><td><b><?php echo __( 'Client' ); ?>:</b></td><td><?php echo $thisclient->getName(); ?></td></tr>
				</table>

			<?php } ?>
		</div>
		
		<!-- /.col -->
		<div class="col-sm-8 col-sm-offset-2">
			<div id="dynamic-form">
				<?php
				if ( $form ) {
					include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
				}
				?>
			</div>
			<?php
			$tform = TicketForm::getInstance();
			if ( $_POST ) {
				$tform->isValidForClient();
			}
			$tform->render( false );
			?>
			<?php
			if ( $cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid()) ) {
				if ( $_POST && $errors && !$errors['captcha'] )
					$errors['captcha'] = __( 'Please re-enter the text again' );
				?>
				<div class="captchaRow">
					<div class="required"><?php echo __( 'CAPTCHA Text' ); ?>:</div>
					<span class="captcha"><img src="captcha.php" border="0" align="left"></span>
					&nbsp;&nbsp;
					<input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
					<em><?php echo __( 'Enter the text shown on the image.' ); ?></em>
					<font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
				</div>
			<?php }
			?>
			<hr>
			<button class="btn btn-success pull-right" type="submit"><i class="fa fa-check"></i> <?php echo __( 'Create Ticket' ); ?></button>
			<button class="btn btn-default" type="reset"><i class="fa fa-refresh"></i> <?php echo __( 'Reset' ); ?></button>
			<button class="btn btn-default" type="button" onclick="javascript:
                            $('.richtext').each(function () {
                        var redactor = $(this).data('redactor');
                        if (redactor && redactor.opts.draftDelete)
                            redactor.deleteDraft();
                    });
                    window.location.href = 'index.php';"><i class="fa fa-times"></i> <?php echo __( 'Cancel' ); ?></button>
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
	
</form>
<script type=\"text/javascript\">
    $(document).ready(function () {
        document.getElementById('_<?php echo $_SESSION ['getField'] ?>').insertAdjacentHTML('afterend', '<a class=\"btn btn-danger text-uppercase\" onclick=\"javascript:gerar_email()\">Gerar um e-mail Anônimo</a>');
    });
    function gerar_email() {
        var alfabeto = 'abcdefghijklmnopqrstuvwxyz';
        var letras = [];
        for (var i = 0; i < 10; ++i)
            letras[i] = alfabeto.charAt(Math.floor(Math.random() * 25));
        var senha = letras.join('') + '@sharklasers.com';
        document.getElementById('_<?php echo $_SESSION ['getField'] ?>').value = senha;
    }
</script> 