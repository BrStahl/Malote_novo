<?php
session_name("covre_ti");
session_start();

include("../../SCA/includes/conect_sqlserver.php");


$id = $_POST["id"];


	$query = "select tipo_malote_id, CONVERT(varchar(10), data_postagem, 103), lacre, num_lacre, nome_expedidor, po_destino_id, destinatario_id, observacao,
			  funcionario, codigo_malote, local_retirada_id
			  from rastreabilidade_malote
			  where id = $id";
	//print $query;
	$result = odbc_exec($conSQL, $query) or die ('erro na SQL');


$registro = "1|".odbc_result($result, 1)."|".odbc_result($result, 2)."|".odbc_result($result, 3)."|".odbc_result($result, 4)."|".odbc_result($result, 5)."|".odbc_result($result, 6)."|".odbc_result($result, 7)."|".odbc_result($result, 8)."|".odbc_result($result, 9)."|".odbc_result($result, 10)."|".odbc_result($result, 11)."|".$id."|";

print $registro;



?>
