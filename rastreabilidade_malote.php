<?php
session_name("covre_ti");
session_start();

require("../SCA/includes/page_func.php");
include("../SCA/includes/conect_sqlserver.php");
require("../SCA/includes/phpmailer/class.phpmailer.php");

$localItem = "../rastreabilidade_malote/rastreabilidade_malote.php";
$logado    = $_SESSION["usuario_logado"];
$acesso	   = valida_acesso($conSQL, $localItem, $logado);
//$acesso = "permitido";

if($acesso <> "permitido"){
    grava_acesso($conSQL, $localItem, $logado, 2, $vObservacao);

    print "
        <script language = 'JavaScript'>
           alert('Acesso negado para esta pagina');
           window.location='centro.php';
		</script>
    ";
}//elseif
else{
	 grava_acesso($conSQL, $localItem, $logado, 1, $vObservacao);

$cod_id 				= $_GET["cd"];

$tipo_malote 			= $_POST["tipo_malote"];
$data_postagem 			= $_POST["data_1"];
$num_lacre 				= $_POST["num_lacre"];
$expedidor 				= $_POST["expedidor"];
$ponto_operacao_id 		= $_POST["ponto_operacao_id"];
$destinatario 			= $_POST["destinatario"];
$observacao 			= $_POST["observacao"];
$malote_id	 			= $_POST["malote_id"];

$data_10 				= $_POST["data_10"];
$data_11	 			= $_POST["data_11"];
$status_id_p 			= $_POST["status_id_p"];
$ponto_operacao_id_p  	= $_POST["ponto_operacao_id_p"];
$destinatario_p			= $_POST["destinatario_p"];
$codigo_malote_p		= $_POST["codigo_malote_p"];

$opcao 					= $_POST["radio"];
$funcionario			= $_POST["funcionario"];
$destinatario_id		= $_POST["destinatario_id"];

$order_by				= $_POST["order_by"];
$agrupar_selecionados   = $_POST["agrupar_selecionados"];
$agrupar_malote         = $_POST["agrupar_malote"];


$observacao  	= str_replace("'", "''", $observacao);


if ($codigo_malote_p != '')
	$cod_id = '';
else
	if ($cod_id != '')
	{
		$codigo_malote_p = 'MA-'.$cod_id;
		//$pesquisar = 'S';
	}

	//BUSCA O USUARIO
	$query = "select DISTINCT usuario.id, usuario.nome, usuario.pessoa_id,
	CASE
		WHEN PSECAO.DESCRICAO LIKE '%SANTOS%'                THEN 15
		WHEN PSECAO.DESCRICAO LIKE '%PS TRANSTEC%'           THEN 0
		WHEN PSECAO.DESCRICAO LIKE '%PS SYNGENTA PAULINIA%'  THEN 43
		WHEN PSECAO.DESCRICAO LIKE '%PS POSTO ALDO%'         THEN 0
		WHEN PSECAO.DESCRICAO LIKE '%PS DELPHI PIRACICABA%'  THEN 45
		WHEN PSECAO.DESCRICAO LIKE '%MATRIZ%'                THEN 1
		WHEN PSECAO.DESCRICAO LIKE '%OSASCO%'                 THEN 16
		WHEN PSECAO.DESCRICAO LIKE '%GUARUJA%'                THEN 112
		WHEN PSECAO.DESCRICAO LIKE '%FILIAL CAMPINAS%'        THEN 58
		WHEN PSECAO.DESCRICAO LIKE '%BOSCH CAMPINAS%'         THEN 71
		WHEN PSECAO.DESCRICAO LIKE '%GUARULHOS%'              THEN 60
		WHEN PSECAO.DESCRICAO LIKE '%GUAXUPE%'                THEN 81
	END AS ponto_operacao_Id,
	CASE
		WHEN PSECAO.DESCRICAO LIKE '%SANTOS%'                THEN 'GUARUJA'
		WHEN PSECAO.DESCRICAO LIKE '%PS TRANSTEC%'           THEN 'TRANSTEC'
		WHEN PSECAO.DESCRICAO LIKE '%PS SYNGENTA PAULINIA%'  THEN 'SYNGENTA'
		WHEN PSECAO.DESCRICAO LIKE '%PS POSTO ALDO%'         THEN 'PS POSTO ALDO'
		WHEN PSECAO.DESCRICAO LIKE '%PS DELPHI PIRACICABA%'  THEN 'PS DELPHI PIRACICABA'
		WHEN PSECAO.DESCRICAO LIKE '%MATRIZ%'                THEN 'MATRIZ'
		WHEN PSECAO.DESCRICAO LIKE '%OSASCO%'                 THEN 'OSASCO'
		WHEN PSECAO.DESCRICAO LIKE '%GUARUJA%'                THEN 'GUARUJA'
		WHEN PSECAO.DESCRICAO LIKE '%FILIAL CAMPINAS%'        THEN 'FILIAL CAMPINAS'
		WHEN PSECAO.DESCRICAO LIKE '%BOSCH CAMPINAS%'         THEN 'BOSCH CAMPINAS'
		WHEN PSECAO.DESCRICAO LIKE '%GUARULHOS%'              THEN 'GUARULHOS'
		WHEN PSECAO.DESCRICAO LIKE '%GUAXUPE%'                THEN 'GUAUXPE'
	END AS ponto_operacao
,chapa
from usuario with (nolock)
	left join CORPORE..PFUNC ON
	usuario.REGISTRO=PFUNC.CHAPA
	LEFT JOIN CORPORE..PSECAO ON
	PSECAO.CODIGO=PFUNC.CODSECAO  
where usuario = '$logado'";
	$result = odbc_exec($conSQL, $query);    
	$usuario_id   = odbc_result($result,1);
	$nome_usuario = odbc_result($result,2);		
	$pessoa_id	  = odbc_result($result,3);		
	$po_usuario	  = odbc_result($result,4);
	$po_origem    = odbc_result($result,5);
	$chapa  = odbc_result($result,6);				
	
	
	if ($order_by == '')
		$order_by = 'order by rm.id';


if($limpar != "")
{
		print"
		<script language='javascript'>
			window.location.href='rastreabilidade_malote.php';
		</script>
		";
}

if($fechar != ""){
		print"
		<script language='javascript'>
			open(location, '_self').close();
		</script>
	";
}


if ($gravar != "")
{
	if ($funcionario != '')
		$funcionario = 'S';

	if ($destinatario_id != '')
	{
		$destinatario_id = rtrim($destinatario_id);
		$destinatario_id = ltrim($destinatario_id);	
	}

	$nova_data_postagem = 
	implode(preg_match("~\/~", $data_postagem) == 0 ? "/" : "-", 
	array_reverse(explode(preg_match("~\/~", $data_postagem) == 0 ? "-" : "/", $data_postagem)));

	if ($tipo_malote == '')
		print "<script type='text/javascript'> alert(unescape('Favor preencher o tipo do malote'));</script>";	
	else
	if ($data_postagem == '')
		print "<script type='text/javascript'> alert(unescape('Favor preencher a data da postagem'));</script>";	
	else
	if ($opcao == '')
		print "<script type='text/javascript'> alert(unescape('Favor preencher se o malote tem lacre'));</script>";	
	else
	if (($opcao == 's') && ($num_lacre == ''))
		print "<script type='text/javascript'> alert(unescape('Favor preencher o n%FAmero do lacre'));</script>";	
	else
	if ($expedidor == '')
		print "<script type='text/javascript'> alert(unescape('Favor preencher o nome do remetente'));</script>";	
	else
	if ($ponto_operacao_id == '')
		print "<script type='text/javascript'> alert(unescape('Favor preencher o destino'));</script>";	
	else
	if (($funcionario != '') && ($destinatario_id == ''))
		print "<script type='text/javascript'> alert(unescape('Favor preencher o nome do destinat%E1rio'));</script>";	
	else							
	{
		if ($malote_id == '')
		{
	
			//BUSCA O ULTIMO MALOTE INSERIDO
			$query = "SELECT TOP 1 'MA-'+right('0000'+RTRIM(id+1),5) CODIGO, ID+1 
					  FROM rastreabilidade_malote
					  ORDER BY ID DESC";
			//print $query;
			$result = odbc_exec($conSQL, $query);    
			$codigo_malote   = odbc_result($result,1);
			$malote_id   	 = odbc_result($result,2);			

			//primeiro codigo
			if ($codigo_malote == '')
				$codigo_malote = 'MA-00001';
	
			$query = "insert into rastreabilidade_malote (tipo_malote_id, data_postagem, recebedor_id, lacre, num_lacre, nome_expedidor, 
					  po_destino_id, observacao, status_id, funcionario, destinatario_id, codigo_malote, sistema,po_origem_id)
					  VALUES ($tipo_malote, '$nova_data_postagem', $usuario_id, '$opcao', '$num_lacre', '$expedidor', $ponto_operacao_id, 
					  '$observacao', 'P', case when '$funcionario' = '' then null else '$funcionario' end, 
					  case when '$destinatario_id' = '' then null else '$destinatario_id' end, '$codigo_malote', 2,'$po_usuario')";
			//print $query;
			odbc_exec($conSQL, $query) or die(odbc_errormsg($conSQL)."<br>Erro ao inserir o malote<br>");		

			// If the flag is set, group selected malotes
			if ($agrupar_selecionados == "S" && !empty($agrupar_malote)) {
				// Get the ID of the newly inserted malote
				$ids_in = implode(",", array_map('intval', $agrupar_malote));
				$query_update = "UPDATE rastreabilidade_malote SET malote_agrupador = '$codigo_malote' WHERE id IN ($ids_in) ";
				odbc_exec($conSQL, $query_update) or die(odbc_errormsg($conSQL)."<br>Erro ao agrupar malotes<br>");
			}

			$insert_update = 1;
			
			print "<script language='javascript'>alert(unescape('N%FAmero do Malote: $codigo_malote'))</script>";
		}
		else
		{
			$query = "update rastreabilidade_malote 
					  set tipo_malote_id = $tipo_malote, data_postagem = '$nova_data_postagem', recebedor_id = $usuario_id, lacre = '$opcao', 
					  num_lacre = '$num_lacre', nome_expedidor = '$expedidor', po_destino_id = $ponto_operacao_id, 
					  observacao = '$observacao',
					  funcionario = case when '$funcionario' = '' then null else '$funcionario' end,
					  destinatario_id = case when '$destinatario_id' = '' then null else '$destinatario_id' end
					  where id = $malote_id";
			//print $query;
			odbc_exec($conSQL, $query) or die(odbc_errormsg($conSQL)."<br>Erro ao atualizar o malote<br>");		
			
			// If the flag is set, group selected malotes
			if ($agrupar_selecionados == "S" && !empty($agrupar_malote)) {
				// Get the ID of the newly inserted malote
				$ids_in = implode(",", array_map('intval', $agrupar_malote));
				$query_update = "UPDATE rastreabilidade_malote SET malote_agrupador = '$codigo_malote' WHERE id IN ($ids_in) ";
				odbc_exec($conSQL, $query_update) or die(odbc_errormsg($conSQL)."<br>Erro ao agrupar malotes<br>");
			}

			$insert_update = 1;
		}	
		
		if ($destinatario_id != '')
		{

			//verifica se o email j� foi enviado
			$query = "select *
					  from log_emails_malote
					  where malote_id = $malote_id";
			//print $query;
			$result = odbc_exec($conSQL, $query) ;
			$malote_enviado = odbc_result($result,1);			

	
			if ($malote_enviado == '')
			{

				//seleciona o email do destinatario
				$query = "select email
						  from usuario
						  where registro = $destinatario_id";
				//print $query;
				$result = odbc_exec($conSQL, $query) ;
				$email_destinatario = odbc_result($result,1);
				
				//seleciona o codigo do malote
				$query = "select codigo_malote
						  from rastreabilidade_malote
						  where id = $malote_id";
				$result = odbc_exec($conSQL, $query) ;
				$cod_malote_enviar = odbc_result($result,1);				

	
				//envia email ao destinatario (se houver)
				$enviou = enviar_email("helpdesk@covre.com.br", "SCA - Rastreabilidade de Malotes", "$email_destinatario", "Malote: $cod_malote_enviar",
				"<font color='#0000FF'>Email autom&aacute;tico</font>
				<br><br><br>$expedidor está enviando o malote $cod_malote_enviar aos seus cuidados.
				<br><br><br><b>SCA - Rastreabilidade de Malotes</b>");
				
				if ($enviou == 1)
				{
					$query2 = "insert into log_emails_malote (malote_id, destinatario_id, data_envio) 
								values ($malote_id, $destinatario_id, getdate())";
					//print $query2;
					odbc_exec($conSQL, $query2);
				}
			}
		}
		
			
	}


}


	
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../SCA/includes/estilo.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../SCA/includes/js/jquery-autocomplete/lib/jquery.js"></script>
<script type="text/javascript" src="../SCA/includes/js/jquery-autocomplete/lib/jquery.bgiframe.min.js"></script>
<script type="text/javascript" src="../SCA/includes/js/jquery-autocomplete/lib/jquery.ajaxQueue.js"></script>
<script type="text/javascript" src="../SCA/includes/js/jquery-autocomplete/lib/thickbox-compressed.js"></script>
<script type="text/javascript" src="../SCA/includes/js/jquery-autocomplete/jquery.autocomplete.js"></script>

<script type="text/javascript" src="../SCA/includes/calendario/_scripts/jquery.click-calendario-1.0-min.js"></script>		
<script type="text/javascript" src="../SCA/includes/calendario/_scripts/exemplo-calendario.js"></script>

<link href="../SCA/includes/calendario/_style/jquery.click-calendario-1.0.css" rel="stylesheet" type="text/css"/>

<link rel="stylesheet" type="text/css" href="../SCA/includes/js/jquery-autocomplete/jquery.autocomplete.css"/>
<link rel="stylesheet" type="text/css" href="../SCA/includes/js/jquery-autocomplete/lib/thickbox.css"/> 

<style type="text/css">
fieldset { padding: 22px 17px 12px 17px; position: relative; margin: 12px 0 34px 0; }

</style>



<script type="text/javascript">
$(document).ready(function(){
	$("#destinatario_p").autocomplete("completar_funcionario.php", {
		width:600,
		selectFirst: false
	});
});
</script>


<script language="javascript">
function altera_dados(valor, id, numero)
{
	if(valor.value != '')
	{
		$.ajax({type: "POST",//define o met�do de passagem de parametros
			url: "includes/altera_dados.php", //chama uma pagina
			data: "valor="+valor+"&id="+id+"&numero="+numero, //parametros
			success: function(msg){  //pega o retorno da pagina chamada
				//alert(msg);
				//if(msg.indexOf("Erro") > -1){
				//	alert("Erro ao alterar a placa");	
					//alert(msg);					
				//}
			}
		});
	}//if
}
</script>

<script language="javascript">
function confirma_retirada(id,i)
{
		var lacre_retirada = document.getElementById("lacre_retirada"+i).value;	
		var data_retirada = document.getElementById("data_retirada"+i).value;
		var resp_retirada = document.getElementById("resp_retirada"+i).value;
	
		//alert(id);
		if(id != '')
		{
			if (data_retirada == '')
			{
				alert(unescape('Favor inserir a Data de Retirada'));
				document.form1.clicar.click();
			}
			else
				if (resp_retirada == '')
				{
					alert(unescape('Favor inserir o Respons%E1vel de Retirada'));
					document.form1.clicar.click();
				}
				else
				{
					$.ajax({type: "POST",//define o met�do de passagem de parametros
						url: "includes/confirma_retirada.php", //chama uma pagina
						data: "id="+id+"&lacre_retirada="+lacre_retirada+"&data_retirada="+data_retirada+"&resp_retirada="+resp_retirada, //parametros
						success: function(msg){  //pega o retorno da pagina chamada
							//alert(msg);
							document.getElementById("lacre_retirada"+i).disabled = true;
							document.getElementById("data_retirada"+i).disabled = true;
							document.getElementById("resp_retirada"+i).disabled = true;
							document.getElementById("confirmar_retirada"+i).disabled = true;
						}
					});
				}
				
		}
	
}
</script>



<script language="javascript">
function valida_agrupamento() {
	if (document.getElementById("agrupar_selecionados").checked) {
		var chks = document.getElementsByName("agrupar_malote[]");
		var origem = null;
		var destino = null;
		var count = 0;
		for (var i = 0; i < chks.length; i++) {
			if (chks[i].checked) {
				count++;
				var o = chks[i].getAttribute("data-origem");
				var d = chks[i].getAttribute("data-destino");
				if (origem == null) origem = o;
				if (destino == null) destino = d;
				if (origem != null && destino != null && (origem != o || destino != d)) {
					alert("S\u00f3 \u00e9 permitido unificar malotes da mesma origem e destino.");
					return false;
				}
			}
		}
		if (count == 0) {
			alert("Nenhum malote selecionado para agrupamento.");
			return false;
		}
	}
	return true;
}

function confirma_malote(id,i)
{

		//alert(id);
		if(id != '')
		{
			if (document.getElementById("data_retirada"+i).value == '')
				alert(unescape('Favor inserir a Data de Retirada'));
			else
				if (document.getElementById("resp_retirada"+i).value == '')
					alert(unescape('Favor inserir o Respons%E1vel de Retirada'));
				else
					if (document.getElementById("data_entrega"+i).value == '')
						alert(unescape('Favor inserir a Data de Entrega'));
					else
					{
						if (confirm(unescape("Deseja realmente confirmar a entrega do malote?")))
						{	
							var data_entrega = document.getElementById("data_entrega"+i).value;
							
							$.ajax({type: "POST",//define o met�do de passagem de parametros
								url: "includes/confirma_malote.php", //chama uma pagina
								data: "id="+id+"&data_entrega="+data_entrega, //parametros
								success: function(msg){  //pega o retorno da pagina chamada
									//alert(msg);
									document.form1.clicar.click();
								}
							});
						}
					}
		}
	
}
</script>

<script language="javascript">
function filtra_funcionario(po)
{	
	//alert(po);
	if (po != "")
	{
		$.ajax({type: "POST",//define o met�do de passagem de parametros
			url: "includes/filtra_funcionario.php", //chama uma pagina
			data: "po="+po, //passa os parametros, se necess�rio
			success: function(msg){  //pega o retorno da pagina chamada
				//alert(msg);
				$("#destinatario_id").html(msg);
			}
		});
	 }
	 else
	 	document.form1.destinatario_id.value = '';

}
</script>

<script language="javascript">
function altera_malote(elmnt)
{
	//alert(logado);

	if (elmnt != ""){
		$.ajax({type: "POST",//define o met�do de passagem de parametros
			url: "includes/busca_malote.php", //chama uma pagina
			data: "id="+elmnt, //passa os parametros, se necess�rio
			success: function(msg)
			{  //pega o retorno da pagina chamada
				//alert(msg);
				var dados = msg.split("|");//quebra a string com base em um caracter

				document.form1.tipo_malote.value 			= dados[1];
				document.form1.data_1.value 				= dados[2];
				
				if (dados[3] == 's')
				{
					document.form1.radio[0].checked = 1;	
					$("#num_lacre").attr({disabled: false}); 
					document.form1.num_lacre.style.background="#FFFFFF";
				
				}
				else
				{
					document.form1.radio[1].checked = 1;						
					$("#num_lacre").attr({disabled: true}); 
					document.form1.num_lacre.style.background="#D3D3D3";					
				}
				
				//document.form1.lacre.value 					= dados[3];
				document.form1.num_lacre.value 				= dados[4];
				document.form1.expedidor.value 				= dados[5];
				document.form1.ponto_operacao_id.value 		= dados[6];
				
				filtra_funcionario(dados[6]);
				
				setTimeout(function()
				{
					document.form1.destinatario_id.value 		= dados[7];
					document.form1.observacao.value 			= dados[8];
	
					if (dados[9] == 'S')
					{
						  document.form1.funcionario.checked = 1;	
						  $("#destinatario_id").attr({disabled: false}); 
						  document.form1.destinatario_id.style.background="#FFFFFF";
					}
					else
					{
						  document.form1.funcionario.checked = 0;					
						  document.form1.destinatario_id.value = '';
						  $("#destinatario_id").attr({disabled: true}); 
						  document.form1.destinatario_id.style.background="#D3D3D3";
					}
	
					document.form1.codigo_malote.value 			= dados[10];				
					document.form1.malote_id.value 				= dados[11];					
				},250);
			}
		});
	}

}
</script>

<script type="text/javascript">
function limpa_campos()
{
	//alert();
	document.form1.tipo_malote.value 			= '';
	document.form1.data_1.value 				= '';
	
	document.form1.radio[0].checked = 0;	
	document.form1.radio[1].checked = 0;						

	document.form1.num_lacre.value 				= '';
	document.form1.expedidor.value 				= '';
	document.form1.ponto_operacao_id.value 		= '';
	document.form1.funcionario.checked = 0;
	document.form1.destinatario_id.value 		= '';
	document.form1.observacao.value 			= '';
	document.form1.malote_id.value 				= '';
	document.form1.destinatario_id.value 		= '';	
	//alert(1);
}
</script>

<script language="javascript">
function habilita_num_lacre(lacre){
	if(document.form1.radio[0].checked == 1)
	{
	  $("#num_lacre").attr({disabled: false}); 
	  document.form1.num_lacre.style.background="#FFFFFF";
	}
    else
		if(document.form1.radio[1].checked == 1)
		{
		  document.form1.num_lacre.value = '';
		  $("#num_lacre").attr({disabled: true}); 
		  document.form1.num_lacre.style.background="#D3D3D3";
		}
}
</script>

<script language="javascript">
function habilita_destinatario(funcionario)
{
	if(document.form1.funcionario.checked == 1)
	{
	  $("#destinatario").attr({disabled: false}); 
	  document.form1.destinatario_id.style.background="#FFFFFF";
	}
    else
	{
	  document.form1.destinatario_id.value = '';	  
	  $("#destinatario").attr({disabled: true}); 
  	  document.form1.destinatario_id.style.background="#D3D3D3";
	}
}
</script>

<script language="javascript">
function esconde_aparece1(reduz1, updown1)
{
  		if($("#"+updown1).html().indexOf("botao_down_big.png") > -1)
		{
        	$("#"+reduz1).show("slow");
			$("#"+updown1).html("<img src='../SCA/images/botao_up_big.png' width='15' height='15' style='border:none'>");
			//alert(1);
		}
		else
		{
	    	$("#"+reduz1).hide("slow");
			$("#"+updown1).html("<img src='../SCA/images/botao_down_big.png' width='15' height='15' style='border:none'>");
			//alert(2);
		}
}
</script>

<script language="javascript">
function busca_pessoa_id(elmnt)
{	
	//alert(elmnt.value);
	if (elmnt.value != ""){
		$.ajax({type: "POST",//define o met�do de passagem de parametros
			url: "includes/busca_pessoa_id.php", //chama uma pagina
			data: "nome="+elmnt.value, //passa os parametros, se necess�rio
			success: function(msg){  //pega o retorno da pagina chamada
						
							//alert(msg);
							var dados = msg.split("|");
							
							if(dados[1].indexOf("invalido") == -1)
								document.form1.destinatario_id.value	= dados[1];
							else
								document.form1.destinatario_id.value	= '';
			}
		});
	}
}
</script>

<script language="javascript">
function copiar(i)
{
	anterior = i-1;
	document.getElementById("lacre_retirada"+i).value =	document.getElementById("lacre_retirada"+anterior).value;
	document.getElementById("data_retirada"+i).value =	document.getElementById("data_retirada"+anterior).value;
	document.getElementById("resp_retirada"+i).value =	document.getElementById("resp_retirada"+anterior).value;		
}
</script>

<script language="javascript">
function altera_ordem(elmnt)
{
	//alert(elmnt);
	if (elmnt != ""){
		$.ajax({type: "POST",//define o met�do de passagem de parametros
			url: "includes/altera_ordem.php", //chama uma pagina
			data: "auxiliar="+elmnt, //passa os parametros, se necess�rio
			success: function(msg){  //pega o retorno da pagina chamada
				//alert(msg);

				document.form1.order_by.value = msg;
				console.debug('form1.order_by', document.form1.order_by)
				//document.form1.submit();

				//$("#programacoes").html(msg);
				//$("#programacoes").show();

			}
		});
	}

}
</script>

</head>
<body>
<form action="" name="form1" method="post" enctype="multipart/form-data" style="width:100%">
<fieldset>
<legend>Rastreabilidade de Malotes</legend> 



<table width="435" border="0" frame="box" rules="none">
	<tr>
	  <td width="6%"><a href="javascript:esconde_aparece1('tabela1','updown1')"><div id="updown1"><img src="../SCA/images/botao_down_big.png" width="15" height="15" style='border:none'/></div></a></td>
	  </tr>
</table>

<div id='tabela1' style='display:none'> 


<table width="500" border="1">
	<tr>
	  <td colspan="2" bgcolor="#CCCCCC"><div align="center"><font color="#0000FF" size="-1"><b>Campos de Pesquisa</b></font></div></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>C&oacute;d. Malote:</b></font></td>
	  <td><input type="text" name="codigo_malote_p" id="codigo_malote_p" size="7" maxlength="10" value="<?php print $codigo_malote_p ?>" onkeyup="mascaraData(this, 'data_1')"/></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>Per&iacute;odo:</b></font></td>
	  <td><input type="text" name="data_10" id="data_10" size="7" maxlength="10" value="<?php print $data_10 ?>" onkeyup="mascaraData(this, 'data_1')"/>
	    <b><font size="-1"> a 
	    <input type="text" name="data_11" id="data_11" size="7" maxlength="10" value="<?php print $data_11 ?>" onkeyup="mascaraData(this, 'data_1')"/>
	    </font></b></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>Tipo Malote:</b></font></td>
	  <td><?php
					$query = "SELECT *
							  FROM tipo_malote";
					$result = odbc_exec($conSQL, $query);           
			  
					print "<select name='tipo_malote_p' id='tipo_malote_p' class='lista' ><option value=''></option>";
					
					while(odbc_fetch_array($result))
					{
						if (odbc_result($result, 1) == $tipo_malote_p)
							$selected = "selected='selected'";
						else
							$selected = "";
	
						 print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
					}     
					print"</select>";
			  ?></td>
	  </tr>
	<tr>
	  <td width="26%"><font size="-1"><b>Num. Lacre:</b></font></td>
	  <td width="74%"><input name="num_lacre_p" type="text" id="num_lacre_p" onblur="javascript:busca_placa_motorista(this)" value="<?php print $num_lacre_p ?>" size="10"/></td>
	</tr>
	<tr>
	  <td><font size="-1"><b>Expedidor:</b></font></td>
	  <td><input type="text" name="expedidor_p" id="expedidor_p" size="30" value="<?php print $expedidor_p ?>" onkeyup="mascaraData(this, 'data_1')"/></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>Destino:</b></font></td>
	  <td><?php
                        $query = "select po.ponto_operacao_id, PO.nome_fantasia 
						from ponto_operacao po (nolock)		 
						order by PO.nome_fantasia
";
                        $result = odbc_exec($conSQL, $query);

                        print "<select name='ponto_operacao_id_p' id='ponto_operacao_id_p' class='lista'>
								<option value=''></option>";

                        while(odbc_fetch_array($result))
                        {

                            if (odbc_result($result, 1) == $ponto_operacao_id_p)
                                $selected = "selected='selected'";
                            else
                                $selected = "";

                             print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
                        }
                        print"</select>";
                ?></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>Destinat&aacute;rio:</b></font></td>
	  <td><input type="text" name="destinatario_p" id="destinatario_p" size="30" value="<?php print $destinatario_p ?>" onkeyup="mascaraData(this, 'data_1')"/></td>
	  </tr>
	<tr>
	  <td><font size="-1"><b>Status:</b></font></td>
	  <td><?php
                        $query = "select 'p', 'Pendentes'
								  union
								  select 'f', 'Entregues'
								  ";
                        $result = odbc_exec($conSQL, $query);

                        print "<select name='status_id_p' id='status_id_p' class='lista' >";

                        while(odbc_fetch_array($result))
                        {

                            if (odbc_result($result, 1) == $status_id_p)
                                $selected = "selected='selected'";
                            else
                                $selected = "";

                             print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
                        }
                        print"</select>";
                ?></td>
	  </tr>
	<tr>
	  <td colspan="2"><input name="pesquisar" type="submit" class="botao_site" value="Pesquisar" id="pesquisar" /></td>
	  </tr>
	</table>
<table width="100%" border="0">        
    <tr>
      <td>&nbsp;</td>
    </tr>
</table>
</div>

  <?php

	//convertendo a data
	$data_inicial = 
	implode(preg_match("~\/~", $data_10) == 0 ? "/" : "-", 
	array_reverse(explode(preg_match("~\/~", $data_10) == 0 ? "-" : "/", $data_10))); 
	 
 	$data_final = 
	implode(preg_match("~\/~", $data_11) == 0 ? "/" : "-", 
	array_reverse(explode(preg_match("~\/~", $data_11) == 0 ? "-" : "/", $data_11))); 




	if ($status_id_p == '')
		$status_id_p = 'p';

	if (($data_inicial != '') && ($data_final != ''))
		$cond_datas = "and data_postagem >= '$data_inicial' and data_postagem < DATEADD(DD,1,'$data_final')";
	else
  		$cond_datas = "";


	if ($codigo_malote_p != '')
		$cond_cod_malote = " and codigo_malote = '$codigo_malote_p'";
	else
  		$cond_cod_malote = "";

  
	if ($tipo_malote_p != '')
		$cond_tipo_malote = " and tipo.id = $tipo_malote_p";
	else
  		$cond_tipo_malote = "";
		
	if ($num_lacre_p != '')
		$cond_num_malote = " and RM.num_lacre = $num_lacre_p";
	else
  		$cond_num_malote = "";		
		
	if ($expedidor_p != '')
		$cond_expedidor = " and RM.nome_expedidor like '%$expedidor_p%'";
	else
  		$cond_expedidor = "";	
		
	if ($ponto_operacao_id_p != '')
		$cond_po_destino = " and RM.po_destino_id = $ponto_operacao_id_p";
	else
  		$cond_po_destino = "";						

	if ($destinatario_p != '')
		$cond_destinatario = " and destinatario.nome like '$destinatario_p%'";
	else
  		$cond_destinatario = "";
		

		$query = "
			select 
				codigo_malote, 
				tipo.descricao tipo_malote, 
				CONVERT(varchar(10), RM.data_postagem, 103) data_postagem, 
				usuario.nome collate sql_latin1_general_cp1251_ci_as recebedor, 
				case 
					when lacre = 'S' then 'Sim'
					else 'Nao'
				end lacre,
				num_lacre, 
				nome_expedidor collate sql_latin1_general_cp1251_ci_as,
				CASE
					WHEN PSECAO.DESCRICAO LIKE '%SANTOS%'                THEN 'GUARUJA'
					WHEN PSECAO.DESCRICAO LIKE '%PS TRANSTEC%'           THEN 'TRANSTEC'
					WHEN PSECAO.DESCRICAO LIKE '%PS SYNGENTA PAULINIA%'  THEN 'SYNGENTA'
					WHEN PSECAO.DESCRICAO LIKE '%PS POSTO ALDO%'         THEN 'PS POSTO ALDO'
					WHEN PSECAO.DESCRICAO LIKE '%PS DELPHI PIRACICABA%'  THEN 'PS DELPHI PIRACICABA'
					WHEN PSECAO.DESCRICAO LIKE '%MATRIZ%'                THEN 'MATRIZ'
					WHEN PSECAO.DESCRICAO LIKE '%OSASCO%'                 THEN 'OSASCO'
					WHEN PSECAO.DESCRICAO LIKE '%GUARUJA%'                THEN 'GUARUJA'
					WHEN PSECAO.DESCRICAO LIKE '%FILIAL CAMPINAS%'        THEN 'FILIAL CAMPINAS'
					WHEN PSECAO.DESCRICAO LIKE '%BOSCH CAMPINAS%'         THEN 'BOSCH CAMPINAS'
					WHEN PSECAO.DESCRICAO LIKE '%GUARULHOS%'              THEN 'GUARULHOS'
					WHEN PSECAO.DESCRICAO LIKE '%GUAXUPE%'                THEN 'GUAUXPE'
				END AS [Filial Origem],
				destinatario.nome collate sql_latin1_general_cp1251_ci_as destinatario, 
				pessoa.nome_fantasia [Filial Destino], 
				RM.observacao observacao, RM.id, 
				convert(varchar(10), rm.data_retirada, 103), 
				rm.responsavel_retirada, 
				rm.recebedor_id, 
				convert(varchar(10), data_recebimento, 103),
				recebedor.nome nom_recebedor_entrega, 
				destinatario_id, 
				rm.funcionario, 
				rm.po_destino_id, 
				rm.lacre_retirada, 
				RM.data_postagem dt_postagem_ordem, 
				rm.data_retirada dt_retirada_ordem, 
				rm.data_recebimento data_recebimento_ordem,
				user_grav_retirada,
				user_grav_ret.nome, RM.malote_agrupador as cod_malote_agrupador, RM.malote_agrupador
			from rastreabilidade_malote RM with (nolock)
				join tipo_malote tipo with (nolock) on
					tipo.id = RM.tipo_malote_id
				join usuario with (nolock) on
					usuario.id = RM.recebedor_id
				join CARGOSOL..PONTO_OPERACAO PO with (nolock) on
					PO.PONTO_OPERACAO_ID = RM.po_destino_id
				join CARGOSOL..PESSOA with (nolock) on
					pessoa.PESSOA_ID = po.PESSOA_ID
				left join usuario recebedor with (nolock) on
					recebedor.id = rm.recebedor_destino_id	
				left join CARGOSOL..PESSOA destinatario with (nolock) on
					destinatario.PESSOA_ID = RM.destinatario_id		
				left join usuario user_grav_ret with (nolock) on
					user_grav_ret.id = RM.user_grav_retirada
				left join CORPORE..PPESSOA ON
					PPESSOA.CPF = destinatario.Pf_Cpf COLLATE SQL_Latin1_General_CP1_CI_AS
				left join CORPORE..PFUNC INATIVA ON
					PPESSOA.CODIGO=INATIVA.CODPESSOA
				left join CORPORE..PFUNC ORIGEM
					ON ORIGEM.CHAPA = usuario.registro
				LEFT JOIN CORPORE..PSECAO 
					ON PSECAO.CODIGO=ORIGEM.CODSECAO  
			where rm.sistema=1 
				AND INATIVA.CODSITUACAO IN ('A','F')
			 	and RM.status_id = '$status_id_p'
				$cond_cod_malote
				$cond_datas
				$cond_tipo_malote
				$cond_num_malote
				$cond_expedidor
				$cond_po_destino
				$cond_destinatario

			union all
			
			select 
				codigo_malote, 
				tipo.descricao tipo_malote, 
				CONVERT(varchar(10), RM.data_postagem, 103) data_postagem, 
				usuario.nome collate sql_latin1_general_cp1251_ci_as recebedor, 
				case 
					when lacre = 'S' then 'Sim'
					else 'Nao'
				end lacre, 
				num_lacre, 
				nome_expedidor collate sql_latin1_general_cp1251_ci_as Rementente,
				CASE
					WHEN PSECAO.DESCRICAO LIKE '%SANTOS%'                THEN 'GUARUJA'
					WHEN PSECAO.DESCRICAO LIKE '%PS TRANSTEC%'           THEN 'TRANSTEC'
					WHEN PSECAO.DESCRICAO LIKE '%PS SYNGENTA PAULINIA%'  THEN 'SYNGENTA'
					WHEN PSECAO.DESCRICAO LIKE '%PS POSTO ALDO%'         THEN 'PS POSTO ALDO'
					WHEN PSECAO.DESCRICAO LIKE '%PS DELPHI PIRACICABA%'  THEN 'PS DELPHI PIRACICABA'
					WHEN PSECAO.DESCRICAO LIKE '%MATRIZ%'                THEN 'MATRIZ'
					WHEN PSECAO.DESCRICAO LIKE '%OSASCO%'                 THEN 'OSASCO'
					WHEN PSECAO.DESCRICAO LIKE '%GUARUJA%'                THEN 'GUARUJA'
					WHEN PSECAO.DESCRICAO LIKE '%FILIAL CAMPINAS%'        THEN 'FILIAL CAMPINAS'
					WHEN PSECAO.DESCRICAO LIKE '%BOSCH CAMPINAS%'         THEN 'BOSCH CAMPINAS'
					WHEN PSECAO.DESCRICAO LIKE '%GUARULHOS%'              THEN 'GUARULHOS'
					WHEN PSECAO.DESCRICAO LIKE '%GUAXUPE%'                THEN 'GUAUXPE'
				END AS [Filial Origem],
				destinatario.nome collate sql_latin1_general_cp1251_ci_as destinatario,
				po.nome_fantasia collate sql_latin1_general_cp1251_ci_as [Filial Destino],
				RM.observacao observacao, 
				RM.id, 
				convert(varchar(10), rm.data_retirada, 103), 
				rm.responsavel_retirada, 
				rm.recebedor_id, 
				convert(varchar(10), data_recebimento, 103), 
				recebedor.nome nom_recebedor_entrega, 
				destinatario_id, 
				funcionario, 
				rm.po_destino_id, 
				rm.lacre_retirada, 
				RM.data_postagem dt_postagem_ordem, 
				rm.data_retirada dt_retirada_ordem, 
				rm.data_recebimento data_recebimento_ordem,
				user_grav_retirada, 
				user_grav_ret.nome, RM.malote_agrupador as cod_malote_agrupador, RM.malote_agrupador
			from rastreabilidade_malote RM with (nolock)
				join tipo_malote tipo with (nolock) 
					on tipo.id = RM.tipo_malote_id
				join usuario with (nolock) 
					on usuario.id = RM.recebedor_id
				join PONTO_OPERACAO PO with (nolock) 
					on PO.PONTO_OPERACAO_ID = RM.po_destino_id
				left join usuario recebedor with (nolock) 
					on recebedor.id = rm.recebedor_destino_id	
				left join CORPORE..PFUNC destinatario 
					ON rm.destinatario_id=destinatario.chapa	
				left join usuario user_grav_ret with (nolock) 
					on user_grav_ret.id = RM.user_grav_retirada
				left join CORPORE..PFUNC INATIVA 
					ON rm.DESTINATARIO_ID=INATIVA.CHAPA
				left join CORPORE..PFUNC ORIGEM
					ON ORIGEM.CHAPA = usuario.registro
				LEFT JOIN CORPORE..PSECAO 
					ON PSECAO.CODIGO=ORIGEM.CODSECAO  	
			where rm.sistema=2
				AND INATIVA.CODSITUACAO IN ('A','F')
				AND DESTINATARIO.CODCOLIGADA = '1'
			
			and RM.status_id = '$status_id_p'
		$cond_cod_malote
		$cond_datas
		$cond_tipo_malote
		$cond_num_malote
		$cond_expedidor
		$cond_po_destino
		$cond_destinatario

	
					
					$order_by";
		//print "<pre>$query</pre>";
		$result = odbc_exec($conSQL, $query);           

		print"<table width='100%' border='1'>
			   <tr>
				 <td bgcolor='#CCCCCC' rowspan='2'><b><center><font size='-2'>Agrupar</font></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'><b><center><font size='-2'>Malote Agrupador</font></center></b></td>

				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(1)'><b><center><font size='-2'>Codigo Malote</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(2)'><b><center><font size='-2'>Tipo Malote</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(3)'><b><center><font size='-2'>Data Postagem</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'>
				 	<a href='javascript:altera_ordem(4)'><b><center><font size='-2'>Usuário Registro</a></center></b></td>				 
				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(5)'><b><center><font size='-2'>N&uacute;mero Lacre</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(6)'><b><center><font size='-2'>Remetente</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'><a href='javascript:altera_ordem(7)'><b><center><font size='-2'>Filial Origem</a></center></b></td>
				 <td bgcolor='#CCCCCC' rowspan='2'>
				 	<a href='javascript:altera_ordem(8)'><b><center><font size='-2'>Destinat&aacute;rio</a></center></b>
				</td>	
				 <td bgcolor='#CCCCCC' rowspan='2'>
				 	<a href='javascript:altera_ordem(9)'><b><center><font size='-2'>Destino</a></center></b>
				</td>				 				 		 				 
				 <td bgcolor='#CCCCCC' rowspan='2'>
				 	<a href='javascript:altera_ordem(10)'><b><center><font size='-2'>Observa&ccedil;&atilde;o</a></center></b></td>";
				 
				 if ($status_id_p == 'p')
				 	print "<td colspan='4' bgcolor='#CCCCCC'><b><center><font size='-2'>Retirada</center></b></td>";
				 else
				 	print "<td colspan='3' bgcolor='#CCCCCC'><b><center><font size='-2'>Retirada</center></b></td>";				 
				
				 print "
				 <td colspan='2' bgcolor='#CCCCCC'><b><center><font size='-2'>Entrega</center></b><b><center><font size='-2'></center></b></td>	
				 </tr>";
				 
				 if ($status_id_p == 'p')
				 	print "<tr>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(11)'><font size='-2'><b><center>Lacre</a></td>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(12)'><font size='-2'><b><center>Data</a></td>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(13)'><font size='-2'><b><center>Responsavel</a></td>
							 <td bgcolor='#CCCCCC'><font size='-2'><b><center>&nbsp;</td>	
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(14)'><font size='-2'><b><center>Data</a></td>
							 <td bgcolor='#CCCCCC'><font size='-2'><b><center>Concluir</td>				 
						   </tr>";
				 else
				 	print "<tr>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(11)'><font size='-2'><b><center>Lacre</a></td>					
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(12)'><font size='-2'><b><center>Data</a></td>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(13)'><font size='-2'><b><center>Responsavel</a></td>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(14)'><font size='-2'><b><center>Data</a></td>
							 <td bgcolor='#CCCCCC'><a href='javascript:altera_ordem(15)'><font size='-2'><b><center>Recebido por</a></td>				 
						   </tr>";				 

			 $i = 1;

			 while(odbc_fetch_row($result))
			 {
				   $codigo_malote_p		= odbc_result($result,1);
				   $tipo_malote_p 		= odbc_result($result,2);
				   $data_postagem_p		= odbc_result($result,3);
				   $recebedor_p 		= odbc_result($result,4);				   
				   $lacre_p 			= odbc_result($result,5);				   
				   $num_lacre_p 		= odbc_result($result,6);				   
				   $nome_expedidor_p	= odbc_result($result,7);
				   $Filial_origem       = odbc_result($result,8);				   				   				   				   						   												
				   $destino_p 			= odbc_result($result,9);				   				   				   				   				   				
				   $destinatario_p 		= odbc_result($result,10);				   				   				   				   						   				
				   $observacao_p 		= odbc_result($result,11);	
				   $id					= odbc_result($result,12);	
				   $data_retirada		= odbc_result($result,13);	
				   $resp_retirada		= odbc_result($result,14);					   				   			   				   				   				   				   $recebedor_id 		= odbc_result($result,14);	
				   $data_receb			= odbc_result($result,16);					   				   			   				   				   				   				   $user_receb	 		= odbc_result($result,16);	
   				   $destinatario_id_p	= odbc_result($result,18);		
   				   $funcionario_p		= odbc_result($result,19);	
   				   $po_destino_id_p		= odbc_result($result,20);	
   				   $lacre_retirada		= odbc_result($result,21);	
   				   $user_grav_retirada	= odbc_result($result,25);		
   				   $nome_user_grav_ret	= odbc_result($result,26);						   			   				   				   					   			   				   
				   
				   if ($user_grav_retirada != '')
				   		$disabled_retirada = 'disabled';
				   else
				   		$disabled_retirada = '';
				   
				print"
				   <tr onmouseover=this.bgColor='#89BFF0' onmouseout=this.bgColor=''>";
				   
				   $cod_malote_agrupador = odbc_result($result, 27);
				   if ($status_id_p == "p") {
					   print "<td bgcolor='#FFFFFF'><center><input type='checkbox' name='agrupar_malote[]' value='$id' class='chk_agrupar' data-origem='$Filial_origem' data-destino='$destino_p' /></center></td>";
				   } else {
					   print "<td bgcolor='#FFFFFF'><center>&nbsp;</center></td>";
				   }
				   print "<td bgcolor='#FFFFFF'><center><font size='-2'>$cod_malote_agrupador</font></center></td>";

				   if (($recebedor_id == $usuario_id) && ($status_id_p == 'p'))
		   			   print "<td bgcolor='#FFFFFF'><center>
					   	<a href='javascript:altera_malote($id)'><font size='-2'>".$codigo_malote_p."</a></center></b></td>";
				   else
		   			   print "<td bgcolor='#FFFFFF'><center><font size='-2'>".$codigo_malote_p."</center></b></td>";

					print "				   	   
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$tipo_malote_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$data_postagem_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$recebedor_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$num_lacre_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$nome_expedidor_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$Filial_origem."</center></b></td>	
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$destino_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$destinatario_p."</center></b></td>
					   <td bgcolor='#FFFFFF'><center><font size='-2'>".$observacao_p."</center></b></td>";
					  
					  
					 if ($status_id_p == 'p')	
					 {
						 print "
						   <td bgcolor='#FFFFFF'><input name='lacre_retirada$i' type='text' id='lacre_retirada$i' value='$lacre_retirada' size='3' 
						   maxlength='10' ondblclick='javacript: copiar($i)' $disabled_retirada/></td>
						   <td bgcolor='#FFFFFF'><input name='data_retirada$i' type='text' id='data_retirada$i' value='$data_retirada' size='7' 
						   maxlength='10' $disabled_retirada/></td>
						   <td bgcolor='#FFFFFF'><input name='resp_retirada$i' type='text' id='resp_retirada$i' value='$resp_retirada' size='20' 
						   maxlength='20' $disabled_retirada title='$nome_user_grav_ret'/></td>	

						   <td bgcolor='#FFFFFF'><center><input name='confirmar_retirada$i' type='button' class='botao_site' value='Ok' 
						   id='confirmar_retirada$i' onclick='javascript: confirma_retirada($id,$i)' $disabled_retirada/></td>						   
						   
						   ";
						  
						  if ((($destinatario_id_p != '') && ($destinatario_id_p == $pessoa_id)) || ($usuario_id == 332) || 
						  		($usuario_id == 118) || ($usuario_id == 121) ||   (($destinatario_id_p != '') && ($destinatario_id_p == $chapa)) ||
								(($usuario_id == 111) && ($po_destino_id_p == 85)) || 
							 	(($usuario_id == 111) && ($po_destino_id_p == 89)) || 
								(($usuario_id == 111) && ($po_destino_id_p == 34)) || 
							 	(($usuario_id == 539) && ($po_destino_id_p == 1)) || 
								(($usuario_id == 87) && ($destinatario_id_p == 3807)) || //Melissa autoriza do Marco
								((($usuario_id == 592) || ($usuario_id == 682)) && ($po_destino_id_p == 1))) //332 - fabio bufo, 111 - Luiz Formoso, 539 - Marcelo Machado, 118 - Pamella Covre, 121 - Rafael Paiva, 592 - Victor Bernardes, 682 - Gustavo Rodrigues
						  	print "
							<td bgcolor='#FFFFFF'>
								<input name='data_entrega$i' type='text' id='data_entrega$i' value='' size='7' maxlength='10'/>
							</td>
							<td bgcolor='#FFFFFF'><center>
								<input name='confirmar$i' type='button' class='botao_site' value='Ok' id='confirmar$i'
							 	onclick='javascript: confirma_malote($id,$i)'/>
							</td>";
						  else
							  if ((($destinatario_id_p == '') && ($po_destino_id_p == $po_usuario)) || ($usuario_id == 332) || 
							  		($usuario_id == 118) || ($usuario_id == 121) || 
									(($usuario_id == 111) && ($po_destino_id_p == 85)) || 
									(($usuario_id == 111) && ($po_destino_id_p == 89)) || 
									(($usuario_id == 111) && ($po_destino_id_p == 34)) || 
							  		(($usuario_id == 539) && ($po_destino_id_p == 1)) || 
									(($usuario_id == 87) && ($destinatario_id_p == 3807)) || //Melissa autoriza do Marco
									((($usuario_id == 592) || ($usuario_id == 682)) && ($po_destino_id_p == 1))) //332 - fabio bufo, 111 - Luiz Formoso, 539 - Marcelo Machado, 118 - Pamella Covre, 121 - Rafael Paiva, 592 - Victor Bernardes, 682 - Gustavo Rodrigues
								print "
								<td bgcolor='#FFFFFF'><input name='data_entrega$i' type='text' id='data_entrega$i' value='' size='7' maxlength='10'/></td>
								<td bgcolor='#FFFFFF'><center><input name='confirmar$i' type='button' class='botao_site' value='Ok' id='confirmar$i'
							     onclick='javascript: confirma_malote($id,$i)'/></td>";
							  else						  
							  	print "
								<td bgcolor='#FFFFFF'><input name='data_entrega$i' type='text' id='data_entrega$d' value='' size='7' maxlength='10' 
								disabled='disabled' style='background: #D3D3D3'/></td>
								<td bgcolor='#FFFFFF'><center><img src='../SCA/images/cadeado1.png' width='15' heigth='15'></td>";					  
					 }
					 else
					 {
						 print "
						   <td bgcolor='#FFFFFF'><center><font size='-2'>".$lacre_retirada."</center></b></td>
						   <td bgcolor='#FFFFFF'><center><font size='-2'>".$data_retirada."</center></b></td>
						   <td bgcolor='#FFFFFF'><center><font size='-2'>".$resp_retirada."</center></b></td>
						   <td bgcolor='#FFFFFF'><center><font size='-2'>".$data_receb."</center></b></td>
						   <td bgcolor='#FFFFFF'><center><font size='-2'>".$user_receb."</center></b></td>";
					 }				   		
									   						   				
				     print "</tr>"; 
					 
				$i++;           
			 } 
		print"</table>";	

	?>
<table width="100%" border="0">        
    <tr>
      <td><input name="order_by" type="hidden" id="order_by" value="<?php print $order_by ?>" size="50" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
</table>
<table width="100%" border="0">        
    <tr>
      <td><font size="-1" color="#0000FF"><b>Cadastrar Malote</b></font></td>
    </tr>
</table>

<table width="100%" border="1" frame="box" rules="none" bgcolor="#C1CDCD">
  <tr>
    <td width="1%">&nbsp;</td>
    <td width="5%"><font size="-1"><b>Tipo Malote:</b></font></td>
    <td width="5%"><font size="-1"><b>Data Post:</b></font></td>
    <td width="14%"><font size="-1"><b>Usuário Registro:</b></font></td>
    <td width="12%"><font size="-1"><b>Lacre:</b></font></td>
    <td width="5%"><font size="-1"><b>Remetente:</b></font></td>
	<td width="5%"><font size="-1"><b>Filial Origem:</b></font></td>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><font size="-1"><b>
      <?php
					$query = "SELECT *
							  FROM tipo_malote";
					$result = odbc_exec($conSQL, $query);           
			  
					print "<select name='tipo_malote' id='tipo_malote' class='lista' ><option value=''></option>";
					
					while(odbc_fetch_array($result))
					{
						if (odbc_result($result, 1) == $tipo_malote)
							$selected = "selected='selected'";
						else
							$selected = "";
	
						 print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
					}     
					print"</select>";
			  ?>
      </b></font></td>
    <td><font size="-1"><b>
      <input name="data_1" type="text" id="data_1" value="<?php print $data_postagem ?>" size="8" maxlength="10" />
      </b></font></td>
    <td><font size="-1">
      <input name="recebedor" type="text" id="recebedor" value="<?php print $nome_usuario ?>" size="50" readonly="readonly"  />
    </font></td>

    <td><font size="-1">
      <input name="radio" type="radio" id="s" value="s" <?php if ($opcao == 's'){print "checked='checked'";} ?> 
          onclick="javascript:habilita_num_lacre(this)"/>
      Sim
      <input type="radio" name="radio" id="n" value="n" <?php if ($opcao == 'n'){print "checked='checked'";} ?>
       	  onclick="javascript:habilita_num_lacre(this)"/>
      N&atilde;o</b>
      <input name="num_lacre" type="text" id="num_lacre" value="<?php print $num_lacre ?>" size="15" disabled="disabled" style="background: #D3D3D3"/>
    </font></td>
    <td><input name="expedidor" type="text" id="expedidor" value="<?php print $nome_usuario ?>" size="40" /></td>
	<td><font size="-1">
      <input name="filial_origem" type="text" id="po_origem" value="<?php print $po_origem ?>" size="20" readonly="readonly" disabled="disabled" />
    </font></td>
    <td colspan="2"><font size="-1"></b></font></td>
  </tr>
	<tr>
	  <td>&nbsp;</td>
	  <td><input name="malote_id" type="hidden" id="malote_id" size="3" value="<?php print $malote_id ?>"/></td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	  <td width="1%">&nbsp;</td>
	  <td width="1%">&nbsp;</td>
	  </tr>
	<tr>
	  <td>&nbsp;</td>
	  <td><font size="-1"><b>Destino:</b></font></td>
	  <td>&nbsp;</td>
	  <td><font size="-1"><b>Destinat&aacute;rio:</b>
	    <input type="checkbox" name="funcionario" id="funcionario" onclick="javascript:habilita_destinatario(this)" 
       <?PHP
	 	  if ($funcionario == 'S')
	  		  print 'checked';	  	 	  
	   ?>         
        />Funcion&aacute;rio
	    &nbsp;
	  </font></td>
	  <td><font size="-1"><b>Observa&ccedil;&atilde;o:</b></font></td>
	  <td><font size="-1"><b>C&oacute;digo Malote:</b></font></td>
	  <td><font size="-1"><b>Agrupar Malotes Selecionados:</b><br /><input type="checkbox" name="agrupar_selecionados" id="agrupar_selecionados" value="S" /></font></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr>
	  <td>&nbsp;</td>
	  <td colspan="2"><font size="-1"></b></font>
	    <?php
                        $query = "select po.ponto_operacao_id, PO.nome_fantasia 
						from ponto_operacao po (nolock)		 
						order by PO.nome_fantasia
";
                        $result = odbc_exec($conSQL, $query);

                        print "<select name='ponto_operacao_id' id='ponto_operacao_id' class='lista' onchange='javascript:filtra_funcionario(this.value)'>
								<option value=''></option>";

                        while(odbc_fetch_array($result))
                        {
							if (odbc_result($result, 1) == $ponto_operacao_id)
								$selected = "selected='selected'";
							else
								$selected = "";
								
                             print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
                        }
                        print"</select>";
                ?></td>
	  <td><?php

				$query = "select ss.subtipo_id, ss.descricao_subtipo
						  From tipo_subtipo tp inner join subtipo_servico ss on tp.subtipo_id = ss.subtipo_id
						  Where tipo_id = '$tipo_servico'";
				//print "$query";
				$result = odbc_exec($conSQL, $query);           
           
           		print "<select name='destinatario_id' id='destinatario_id' class='lista'><option value=''></option>";
					
       			while(odbc_fetch_array($result))
           		{
					if (odbc_result($result, 1) == $destinatario_id)
						$selected = "selected='selected'";
					else
						$selected = "";
					
            		print "<option value='".odbc_result($result, 1)."'$selected>".odbc_result($result, 2)."</option>";
           		}     
          		print"</select>";		  


		  ?></td>
	  <td><input name="observacao" type="text" id="observacao" value="<?php print $observacao ?>" size="43" /></td>
	  <td><input align="center" name="codigo_malote" type="text" id="codigo_malote" size="9" maxlength="12" value="" readonly="readonly" style="background: #D3D3D3"/></td>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	  </tr>
	<tr>
	  <td colspan="8"><div align="center"></div></td>
	  </tr>
	<tr>
	  <td colspan="8"><div align="center"><span class="txt_home">
	    <input name="gravar" type="submit" class="botao_site" value=" Gravar " id="gravar" onclick="return valida_agrupamento();" />
	    </span></div></td>
	  </tr>
	</table>
<div id="clicar" style="display:none">    
<table width="479" border="0" align="center">
	<tr>
	  <td width="473"><span class="txt_home">
	    <input name="clicar" type="submit" class="botao_site" value="clicar" id="clicar" />
	  </span></td>
	  </tr>
</table>
</div>


<table width="619" border="0" align="center">        
        <tr>
          <td width="544" class="txt_home">&nbsp;</td>
          <td width="65" class="txt_home">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" class="txt_home"><div align="center">
            <input name="limpar" type="submit" class="botao_site" value=" Limpar " id="limpar" />            
            <input name="fechar" type="submit" class="botao_site" value=" Fechar " id="fechar" />
          </div></td>
        </tr>
</table>


 </fieldset>
</form>

</body>
</html>
<?php

	if ($insert_update == 1)
	{
		print "<script language='javascript'>limpa_campos()</script>";
		$insert_update = 0;
	}

	if ($opcao == 's')
	  print "<script language='javascript'>habilita_num_lacre('s')</script>";		
	else
	  print "<script language='javascript'>habilita_num_lacre('n')</script>";			

	if ($funcionario == 'S')
	  print "<script language='javascript'>habilita_destinatario('s')</script>";		
	else
	  print "<script language='javascript'>habilita_destinatario('n')</script>";
	  
	if ($ponto_operacao_id != '')
	  print "<script language='javascript'>filtra_funcionario($ponto_operacao_id)</script>";		  		

	
}//else
?>
