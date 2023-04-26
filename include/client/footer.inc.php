</div>
<!-- /.container -->
</section>
<!-- /#main -->

<footer>
    <div class="container text-center">
        <div class="row">            
            <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ? : 'fortes.ind.br'; ?>. Todos os Direitos Reservados.</p>
            <p><a href="http://ouvidoria.fortes.ind.br" target="_blank"><?php echo __('Programa de Integridade da Fortes Engenharia'); ?></a></p>            
        </div>
    </div>
    <!-- /.container -->
</footer>


<div id="overlay"></div>
<div id="loading">
    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Aguarde um momento...</h4>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                            <span class="sr-only">100% Completo</span>
                        </div>
                    </div>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>
<!-- /.loading -->

<!-- Scripts -->

<?php
if ($getField_email) {
    echo "<script type=\"text/javascript\">
    $(document).ready(function() {
    	document.getElementById('_" . $getField_email . "').insertAdjacentHTML('afterend', '<a class=\"btn btn-danger text-uppercase\" onclick=\"javascript:gerar_email()\"><i class=\"fa fa-2x fa-user-secret\" aria-hidden=\"true\"></i> Criar e-mail Anônimo</a>');
    });
						
    function gerar_email() {
		var alfabeto = 'abcdefghijklmnopqrstuvwxyz';
		var letras = [];
        for (var i = 0; i < 10; ++i)
        letras[i] = alfabeto.charAt(Math.floor(Math.random() * 25));            
        var senha = letras.join('') + '@sharklasers.com';
        document.getElementById('_" . $getField_email . "').value = senha;
    }
</script> ";
}
?>
<!-- /Scripts -->

<?php if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'pt_BR') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php echo $lang; ?>/js"></script>
<?php } ?>
</body>
</html>
