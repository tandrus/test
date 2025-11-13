<?php

namespace App\Repository;

use App\Entity\Voucher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voucher>
 */
class VoucherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voucher::class);
    }

    public function create(string $code, int $discount): Voucher
    {
        $voucher = new Voucher();
        $voucher->setDiscount($discount);
        $voucher->setCode($code);

        return $voucher;
    }

    public function save(Voucher $voucher, bool $flush = true): void
    {
        $this->getEntityManager()->persist($voucher);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
