<?php

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Reserve inventory for pending order
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        $inventory = $product->getInventory();
        if (!$inventory) {
            return false;
        }

        return $inventory->reserve($quantity);
    }

    /**
     * Release reserved inventory (cancel order)
     */
    public function unreserveStock(Product $product, int $quantity): void
    {
        $inventory = $product->getInventory();
        if ($inventory) {
            $inventory->unreserve($quantity);
            $this->em->flush();
        }
    }

    /**
     * Decrease inventory on order completion
     */
    public function decreaseStock(Product $product, int $quantity): void
    {
        $inventory = $product->getInventory();
        if ($inventory) {
            $inventory->decreaseStock($quantity);
            $inventory->unreserve($quantity);
            $this->em->flush();
        }
    }

    /**
     * Restock inventory
     */
    public function restockInventory(Product $product, int $quantity): void
    {
        $inventory = $product->getInventory();
        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->setProduct($product);
            $this->em->persist($inventory);
        }

        $wasOutOfStock = $inventory->isOutOfStock();
        $inventory->increaseStock($quantity);

        // Notify if was out of stock and now back in stock
        if ($wasOutOfStock && !$inventory->isOutOfStock()) {
            $this->notifyBackInStock($product);
        }

        $this->em->flush();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(): array
    {
        return $this->em->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', 'low_stock')
            ->orWhere('i.status = :out_stock')
            ->setParameter('out_stock', 'out_of_stock')
            ->orderBy('i.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get products needing reorder
     */
    public function getProductsNeedingReorder(): array
    {
        return $this->em->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->where('i.quantity < i.minThreshold')
            ->andWhere('i.reorderQuantity IS NOT NULL')
            ->orderBy('i.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Auto-reorder inventory
     */
    public function autoReorder(): int
    {
        $items = $this->getProductsNeedingReorder();
        $restocked = 0;

        foreach ($items as $inventory) {
            if ($inventory->getReorderQuantity()) {
                $inventory->increaseStock($inventory->getReorderQuantity());
                $restocked++;
            }
        }

        $this->em->flush();
        return $restocked;
    }

    /**
     * Notify users about back in stock
     */
    private function notifyBackInStock(Product $product): void
    {
        // Find users who wishlisted this product
        $wishlistItems = $this->em->getRepository(\App\Entity\Wishlist::class)
            ->findBy(['product' => $product]);

        foreach ($wishlistItems as $wishlist) {
            // Send notification via NotificationService
            // This will be called from a notification service
        }
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStats(): array
    {
        $stats = $this->em->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->select('
                COUNT(i.id) as totalProducts,
                SUM(i.quantity) as totalQuantity,
                SUM(i.reserved) as totalReserved,
                SUM(CASE WHEN i.status = :out_stock THEN 1 ELSE 0 END) as outOfStockCount,
                SUM(CASE WHEN i.status = :low_stock THEN 1 ELSE 0 END) as lowStockCount
            ')
            ->setParameter('out_stock', 'out_of_stock')
            ->setParameter('low_stock', 'low_stock')
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'totalProducts' => (int)($stats['totalProducts'] ?? 0),
            'totalQuantity' => (int)($stats['totalQuantity'] ?? 0),
            'totalReserved' => (int)($stats['totalReserved'] ?? 0),
            'outOfStockCount' => (int)($stats['outOfStockCount'] ?? 0),
            'lowStockCount' => (int)($stats['lowStockCount'] ?? 0),
        ];
    }
}
