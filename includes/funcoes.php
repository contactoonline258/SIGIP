<?php
/**
 * Calcula o preço da encadernação em blocos/escalões de 50 folhas (30 MT por bloco)
 */
function calcularPrecoEncadernacao(float $folhas): float {
    if ($folhas <= 0) {
        return 0.00;
    }

    $blocos = ceil($folhas / 50);
    return $blocos * 30.00;
}