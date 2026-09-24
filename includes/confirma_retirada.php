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

	// If this malote belongs to a group, we want to update the parent and all siblings.
	// We'll figure out the root grouped malote id first.
	$query_parent = "SELECT ISNULL(malote_agrupador_id, id) FROM rastreabilidade_malote WHERE id = $id";
	$res_parent = odbc_exec($conSQL, $query_parent);
	$parent_id = odbc_result($res_parent, 1);

	if (!$parent_id) {
	    $parent_id = $id; // Fallback
	}

	$query = "update rastreabilidade_malote
			  set data_retirada = '$nova_data_retirada', responsavel_retirada = '$resp_retirada', lacre_retirada = '$lacre_retirada',
			  user_grav_retirada = $usuario_id, data_gravacao_retirada = getdate()
			  where id = $parent_id OR malote_agrupador_id = $parent_id";
	//print $query;
	odbc_exec($conSQL, $query) or die("Erro ao atualizar a confirmacao da retirada");

}
?>
