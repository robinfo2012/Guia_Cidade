<?php if($b['plan'] !== 'free'): ?>
  <div class="mt-6 bg-white shadow rounded-lg p-6 text-center">
    <h4 class="text-lg font-semibold text-gray-700 mb-3">Assinar Plano</h4>
    <p class="mb-4">Valor: 
      <span class="font-bold text-blue-600">
        R$ <?php echo number_format($planos[$b['plan']]['preco'],2,',','.');?>
      </span>
    </p>
    <img src="qrcode_pix.php?valor=<?php echo $planos[$b['plan']]['preco'];?>" class="mx-auto mb-4">
    <p class="text-sm text-gray-500">Escaneie o QR Code para pagar via Pix</p>
  </div>
<?php endif; ?>

