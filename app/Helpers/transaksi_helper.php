<?php

function hitung_ppn($total_harga)
{
    return $total_harga * 0.11;
}

function hitung_biaya_admin($total_harga)
{
    if ($total_harga <= 20000000) {
        return $total_harga * 0.006;
    } elseif ($total_harga <= 40000000) {
        return $total_harga * 0.008;
    } else {
        return $total_harga * 0.01;
    }
}

function hitung_diskon_voucher($total_harga, $voucher_code)
{
    $vouchers = [
        'FLASH10'  => 0.10,
        'FLASH15'  => 0.15,
        'MEMBER20' => 0.20,
    ];

    $kode = strtoupper($voucher_code);

    if (isset($vouchers[$kode])) {
        return $total_harga * $vouchers[$kode];
    }

    return 0;
}