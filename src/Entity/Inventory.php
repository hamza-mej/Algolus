<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'inventory')]
#[ORM\Index(columns: ['product_id'])]
#[ORM\Index(columns: ['quantity'])]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'inventory')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 0;

    #[ORM\Column(type: 'integer')]
    private int $reserved = 0;

    #[ORM\Column(type: 'integer')]
    private int $minThreshold = 10;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $reorderQuantity = 100;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'in_stock'; // in_stock, low_stock, out_of_stock, discontinued

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastUpdated;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastRestocked = null;

    public function __construct()
    {
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getProduct(): Product { return $this->product; }
    public function setProduct(Product $product): self { $this->product = $product; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $qty): self { $this->quantity = $qty; $this->updateStatus(); return $this; }
    public function getReserved(): int { return $this->reserved; }
    public function setReserved(int $reserved): self { $this->reserved = $reserved; return $this; }
    public function getAvailable(): int { return max(0, $this->quantity - $this->reserved); }
    public function getMinThreshold(): int { return $this->minThreshold; }
    public function setMinThreshold(int $min): self { $this->minThreshold = $min; return $this; }
    public function getReorderQuantity(): ?int { return $this->reorderQuantity; }
    public function setReorderQuantity(?int $qty): self { $this->reorderQuantity = $qty; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getLastUpdated(): \DateTimeImmutable { return $this->lastUpdated; }
    public function getLastRestocked(): ?\DateTimeImmutable { return $this->lastRestocked; }
    public function setLastRestocked(?\DateTimeImmutable $date): self { $this->lastRestocked = $date; return $this; }

    public function decreaseStock(int $amount): void
    {
        $this->quantity = max(0, $this->quantity - $amount);
        $this->lastUpdated = new \DateTimeImmutable();
        $this->updateStatus();
    }

    public function increaseStock(int $amount): void
    {
        $this->quantity += $amount;
        $this->lastUpdated = new \DateTimeImmutable();
        $this->lastRestocked = new \DateTimeImmutable();
        $this->updateStatus();
    }

    public function reserve(int $amount): bool
    {
        if ($this->getAvailable() >= $amount) {
            $this->reserved += $amount;
            return true;
        }
        return false;
    }

    public function unreserve(int $amount): void
    {
        $this->reserved = max(0, $this->reserved - $amount);
    }

    public function isLowStock(): bool
    {
        return $this->getAvailable() <= $this->minThreshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    public function needsReorder(): bool
    {
        return $this->getAvailable() < $this->minThreshold;
    }

    private function updateStatus(): void
    {
        if ($this->quantity === 0) {
            $this->status = 'out_of_stock';
        } elseif ($this->getAvailable() <= $this->minThreshold) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'in_stock';
        }
    }
}
