<?php
// models/CartModel.php

class CartModel {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function getItems(): array {
        return $_SESSION['cart'] ?? [];
    }

    public function add(int $productId, string $name, float $price, string $image, string $color, string $size, int $qty): void {
        $key = "{$productId}_{$color}_{$size}";
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = [
                'id'    => $productId,
                'name'  => $name,
                'price' => $price,
                'image' => $image,
                'color' => $color,
                'size'  => $size,
                'qty'   => $qty
            ];
        }
    }

    public function setBuyNow(array $item): void {
        $_SESSION['checkout_mode'] = 'buy_now';
        $_SESSION['buy_now_item']  = $item;
    }

    public function setSelectedKeys(array $keys): void {
        $selected = [];
        foreach ($keys as $k) {
            if (isset($_SESSION['cart'][$k])) {
                $selected[$k] = $_SESSION['cart'][$k];
            }
        }
        $_SESSION['checkout_selected'] = $selected;
        $_SESSION['checkout_mode'] = 'selected';
    }

    // Lấy danh sách sản phẩm thực sự được chọn để thanh toán
    public function getCheckoutItems(): array {
        if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'buy_now' && isset($_SESSION['buy_now_item'])) {
            return [$_SESSION['buy_now_item']];
        }
        if (!empty($_SESSION['checkout_selected'])) {
            return $_SESSION['checkout_selected'];
        }
        return $_SESSION['cart'] ?? [];
    }

    public function updateQuantity(string $key, int $qty): void {
        if (isset($_SESSION['cart'][$key])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['qty'] = $qty;
            }
        }
    }

    public function remove(string $key): void {
        unset($_SESSION['cart'][$key]);
    }

    public function clearAfterOrder(): void {
        if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'buy_now') {
            unset($_SESSION['buy_now_item']);
            unset($_SESSION['checkout_mode']);
            return;
        }
        if (!empty($_SESSION['checkout_selected'])) {
            foreach (array_keys($_SESSION['checkout_selected']) as $k) {
                unset($_SESSION['cart'][$k]);
            }
            unset($_SESSION['checkout_selected']);
            unset($_SESSION['checkout_mode']);
        } else {
            $_SESSION['cart'] = [];
        }
    }

    public function calculateTotal(array $items): float {
        $sum = 0;
        foreach ($items as $item) {
            $sum += (float)$item['price'] * (int)$item['qty'];
        }
        return $sum;
    }

    // Phí ship: Miễn phí đơn >= 1.000.000đ, ngược lại 30.000đ
    public function calculateShippingFee(float $subtotal): float {
        return ($subtotal >= 1000000 || $subtotal == 0) ? 0 : 30000;
    }
}