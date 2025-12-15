<?php

namespace App\Controller;

use App\Service\Discount as DiscountService;
use App\Service\Voucher as VoucherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ApplyController extends AbstractController
{
    public function __construct(
        private readonly VoucherService $voucherService,
        private readonly DiscountService $discountService,
    ) {
    }

    #[Route('/apply', name: 'apply', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);
        $code = $requestContent['code'];
        $items = $requestContent['items'];

        $voucher = $this->voucherService->getVoucher($code);
        $voucherDiscount = $voucher->getDiscount();

        $itemsWithDiscount = $this->discountService->applyVoucher($items, $voucherDiscount);

        return $this->json([
            'items' => $itemsWithDiscount,
            'code' => $voucher->getCode(),
        ]);
    }
}
