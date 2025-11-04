<?php
require 'phpqrcode/qrlib.php';
include 'config.php';

function gerarPixPayload($chave, $valor, $nomeRecebedor="Guia da Cidade", $cidade="BRASIL"){
    $valorFormatado = number_format($valor, 2, '.', '');
    return "00020126".strlen("0014BR.GOV.BCB.PIX01".$chave).
           "0014BR.GOV.BCB.PIX01".$chave.
           "52040000".
           "5303986".
           "54".str_pad(strlen($valorFormatado),2,"0",STR_PAD_LEFT).$valorFormatado.
           "5802BR".
           "590" . strlen($nomeRecebedor) . $nomeRecebedor .
           "600" . strlen($cidade) . $cidade .
           "62070503***";
}

$chavePix = getSetting('pix_key') ?? '03489520580';
$valor = isset($_GET['valor']) ? floatval($_GET['valor']) : 0.01;

$payload = gerarPixPayload($chavePix, $valor);

header('Content-Type: image/png');
QRcode::png($payload, false, QR_ECLEVEL_M, 6);
