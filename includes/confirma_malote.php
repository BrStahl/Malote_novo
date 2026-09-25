<?php
session_name("covre_ti");
session_start();

include("../../SCA/includes/conect_sqlserver.php");

$logado = $_SESSION["usuario_logado"];

$id			 	= $_POST["id"];
$data_entrega	= $_POST["data_entrega"];


if($logado != "")
{
	$query = "select id
			  From usuario with (nolock)
			  Where usuario = '$logado'";
	//print $query;
	$result = odbc_exec($conSQL, $query) ;
	$usuario_id = odbc_result($result, 1);


	$nova_data_entrega =
	implode(preg_match("~\/~", $data_entrega) == 0 ? "/" : "-",
	array_reverse(explode(preg_match("~\/~", $data_entrega) == 0 ? "-" : "/", $data_entrega)));

	// Determine if this is part of a group
	$query_parent = "SELECT ISNULL(malote_agrupador, codigo_malote) FROM rastreabilidade_malote WHERE id = $id";
	$res_parent = odbc_exec($conSQL, $query_parent);
	$parent_code = odbc_result($res_parent, 1);

	if (!$parent_code) {
		// Fallback
		$query = "update rastreabilidade_malote
				  set data_recebimento = '$nova_data_entrega', recebedor_destino_id = $usuario_id, status_id = 'f'
				  where id = $id";
	} else {
		// Update parent and all children
		$query = "update rastreabilidade_malote
				  set data_recebimento = '$nova_data_entrega', recebedor_destino_id = $usuario_id, status_id = 'f'
				  where codigo_malote = '$parent_code' OR malote_agrupador = '$parent_code'";
	}

	//print $query;
	odbc_exec($conSQL, $query) or die("Erro ao atualizar a confirmacao do malote");

}
?>
