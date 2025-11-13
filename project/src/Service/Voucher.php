<?php

namespace App\Service;

use App\Entity\Voucher as EntityVoucher;
use App\Repository\VoucherRepository;

class Voucher
{
    public function __construct(private readonly VoucherRepository $voucherRepository,)
    {

    }

    public function createVoucher(string $discount, int $lengthCode): EntityVoucher
    {
        $code = $this->generateCode($lengthCode);
        $voucher = $this->voucherRepository->create($code, (int)$discount);
        $this->voucherRepository->save($voucher);

        return $voucher;
    }

    public function getVoucher(string $code): EntityVoucher
    {
        return $this->voucherRepository->findOneBy(['code' => $code]);
    }

    private function generateCode(int $length): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}