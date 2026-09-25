<?php
session_name("covre_ti");
session_start();

include("../../SCA/includes/conect_sqlserver.php");

$logado = $_SESSION["usuario_logado"];

$id			 	= $_POST["id"];
$lacre_retirada	= $_POST["lacre_retirada"];
$data_retirada	= $_POST["data_retirada"];
$resp_retirada	= $_POST["resp_retirada"];


if($logado != "")
{
	$query = "select id
			  From usuario with (nolock)
			  Where usuario = '$logado'";
	//print $query;
	$result = odbc_exec($conSQL, $query) ;
	$usuario_id = odbc_result($result, 1);


	$nova_data_retirada =
	implode(preg_match("~\/~", $data_retirada) == 0 ? "/" : "-",
	array_reverse(explode(preg_match("~\/~", $data_retirada) == 0 ? "-" : "/", $data_retirada)));

	// Determine if this is part of a group
	$query_parent = "SELECT ISNULL(malote_agrupador, codigo_malote) FROM rastreabilidade_malote WHERE id = $id";
	$res_parent = odbc_exec($conSQL, $query_parent);
	$parent_code = odbc_result($res_parent, 1);

	if (!$parent_code) {
		// Fallback query only for this id if something goes wrong
	    $query = "update rastreabilidade_malote
			      set data_retirada = '$nova_data_retirada', responsavel_retirada = '$resp_retirada', lacre_retirada = '$lacre_retirada',
			      user_grav_retirada = $usuario_id, data_gravacao_retirada = getdate()
			      where id = $id";
	} else {
		// Update parent and all children
		$query = "update rastreabilidade_malote
				  set data_retirada = '$nova_data_retirada', responsavel_retirada = '$resp_retirada', lacre_retirada = '$lacre_retirada',
				  user_grav_retirada = $usuario_id, data_gravacao_retirada = getdate()
				  where codigo_malote = '$parent_code' OR malote_agrupador = '$parent_code'";
	}

	//print $query;
	odbc_exec($conSQL, $query) or die("Erro ao atualizar a confirmacao da retirada");

}
?>
