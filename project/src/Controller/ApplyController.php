<?php

namespace App\Controller;

use App\Service\Voucher as VoucherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ApplyController extends AbstractController
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    #[Route('/apply', name: 'apply', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);
        $code = $requestContent['code'];
        $items = $requestContent['items'];

        $voucher = $this->voucherService->getVoucher($code);
        $voucherDiscount = $voucher->getDiscount();
        $voucherCode = $voucher->getCode();

        $totalPrice = array_sum(array_column($items, 'price'));
        $coefDiscount = $voucherDiscount / $totalPrice;

        $responseItems = [];
        if ($coefDiscount >= 1) {
            foreach ($items as $item) {
                $responseItems['items'][] = [
                    'id' => $item['id'],
                    'price' => $item['price'],
                    'price_with_discount' => 0,
                ];
            }
            $responseItems['code'] = $voucherCode;

            return $this->json($responseItems);
        }

        // Calculate exact discount for each item and separate integer/fractional parts
        $itemsWithDiscount = [];
        foreach ($items as $index => $item) {
            $exactDiscount = ($item['price'] / $totalPrice) * $voucherDiscount;
            $itemsWithDiscount[] = [
                'index' => $index,
                'integerDiscount' => (int) floor($exactDiscount),
                'fractional' => $exactDiscount - floor($exactDiscount),
                'price' => $item['price']
            ];
        }

        // Calculate how many remainder units we need to distribute
        $totalIntegerDiscount = array_sum(array_column($itemsWithDiscount, 'integerDiscount'));
        $remainder = $voucherDiscount - $totalIntegerDiscount;

        // Sort by fractional part (highest first), then by price (highest first)
        usort($itemsWithDiscount, function($a, $b) {
            $fracCompare = $b['fractional'] <=> $a['fractional'];
            if ($fracCompare !== 0) {
                return $fracCompare;
            }
            return $b['price'] <=> $a['price'];
        });

        // Distribute remainder to items with highest fractional parts
        foreach ($itemsWithDiscount as $i => $data) {
            $itemDiscount = $data['integerDiscount'] + ($i < $remainder ? 1 : 0);
            $items[$data['index']]['price_with_discount'] = $items[$data['index']]['price'] - $itemDiscount;
        }

        return $this->json([
            'items' => array_values($items),
            'code' => $voucherCode,
        ]);
    }

    public function myNonWorkingMethod(Request $request)
    {
        $requestContent = json_decode($request->getContent(), true);
        $code = $requestContent['code'];
        $items = $requestContent['items'];

        $voucher = $this->voucherService->getVoucher($code);
        $voucherDiscount = $voucher->getDiscount();
        $voucherCode = $voucher->getCode();

        $totalDiscount = 0;
        $countItems = count($items);
        $i = 0;

        foreach ($items as $item) {
            if (++$i === $countItems) {
                $itemDiscount = max(0, $voucherDiscount - $totalDiscount);
            } else {
                $itemDiscount = round($item['price'] * $coefDiscount);
                $itemDiscount = min($itemDiscount, $voucherDiscount - $totalDiscount);
            }
            $totalDiscount += $itemDiscount;

            $responseItems['items'][] = [
                'id' => $item['id'],
                'price' => $item['price'],
                'price_with_discount' => $item['price'] - $itemDiscount,
            ];
        }
        $responseItems['code'] = $voucherCode;

        return $this->json($responseItems);
    }
}
