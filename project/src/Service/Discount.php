<?php

namespace App\Service;

class Discount
{
    public function applyVoucher(array $items, int $voucherDiscount): array
    {
        $totalPrice = array_sum(array_column($items, 'price'));

        if ($voucherDiscount >= $totalPrice) {
            foreach ($items as &$item) {
                $item['price_with_discount'] = 0;
            }
            return $items;
        }

        // Вираховуємо точний дисконт на кожен товар
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

        // Скільки одиниць залишку треба розподілити
        $totalIntegerDiscount = array_sum(array_column($itemsWithDiscount, 'integerDiscount'));
        $remainder = $voucherDiscount - $totalIntegerDiscount;

        // Сортуємо по дробовій частині (спершу найбільші), потім по ціні
        usort($itemsWithDiscount, function($a, $b) {
            $fracCompare = $b['fractional'] <=> $a['fractional'];
            if ($fracCompare !== 0) return $fracCompare;
            return $b['price'] <=> $a['price'];
        });

        // Розподіляємо залишок
        foreach ($itemsWithDiscount as $i => $data) {
            $itemDiscount = $data['integerDiscount'] + ($i < $remainder ? 1 : 0);
            $items[$data['index']]['price_with_discount'] = $items[$data['index']]['price'] - $itemDiscount;
        }

        return $items;
    }
}
