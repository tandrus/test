<?php

namespace App\Controller;

use App\Service\Voucher as VoucherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateController extends AbstractController
{
    private const int CODE_LENGTH = 7;

    public function __construct(
        private readonly VoucherService $voucherService,
    ) {
    }

    #[Route('/generate', name: 'app_home', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $requestContent = $request->getContent();
        $discount = json_decode($requestContent, true)['discount'];

        try {
            $voucher = $this->voucherService->createVoucher($discount, self::CODE_LENGTH);
        } catch (\Throwable $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        return $this->json([
            'code' => $voucher->getCode(),
        ]);
    }
}
