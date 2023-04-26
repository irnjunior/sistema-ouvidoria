<?php

function randString($size) {
	//String com valor possíveis do resultado, os caracteres pode ser adicionado ou retirados conforme sua necessidade
	$basic = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	$return = "";

	for ( $count = 0; $size > $count; $count++ ) {
		//Gera um caracter aleatorio
		$return.= $basic[rand( 0, strlen( $basic ) - 1 )];
	}

	return $return;
}

//Imprime uma String randônica com 20 caracteres
//echo randString( 20 );
