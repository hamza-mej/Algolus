<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ab_test')]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
class ABTest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type; // email_subject, product_title, product_price, product_image, landing_page

    #[ORM\Column(type: 'json')]
    private array $variantA = [];

    #[ORM\Column(type: 'json')]
    private array $variantB = [];

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'draft'; // draft, running, paused, completed

    #[ORM\Column(type: 'float')]
    private float $splitPercentage = 50; // 0-100

    #[ORM\Column(type: 'string', length: 50)]
    private string $metric = 'click_rate'; // click_rate, conversion_rate, engagement_rate

    #[ORM\Column(type: 'integer')]
    private int $variantAViews = 0;
    #[ORM\Column(type: 'integer')]
    private int $variantAConversions = 0;
    #[ORM\Column(type: 'integer')]
    private int $variantBViews = 0;
    #[ORM\Column(type: 'integer')]
    private int $variantBConversions = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $winner = null;

    public function __construct()
    {
        $this->startDate = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): self { $this->description = $desc; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getVariantA(): array { return $this->variantA; }
    public function setVariantA(array $data): self { $this->variantA = $data; return $this; }
    public function getVariantB(): array { return $this->variantB; }
    public function setVariantB(array $data): self { $this->variantB = $data; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getSplitPercentage(): float { return $this->splitPercentage; }
    public function setSplitPercentage(float $percentage): self { $this->splitPercentage = $percentage; return $this; }
    public function getMetric(): string { return $this->metric; }
    public function setMetric(string $metric): self { $this->metric = $metric; return $this; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(?\DateTimeImmutable $date): self { $this->endDate = $date; return $this; }
    public function getWinner(): ?string { return $this->winner; }
    public function setWinner(?string $winner): self { $this->winner = $winner; return $this; }

    public function getVariantAConversionRate(): float
    {
        return $this->variantAViews > 0 ? ($this->variantAConversions / $this->variantAViews) * 100 : 0;
    }

    public function getVariantBConversionRate(): float
    {
        return $this->variantBViews > 0 ? ($this->variantBConversions / $this->variantBViews) * 100 : 0;
    }

    public function recordView(string $variant): void
    {
        if ($variant === 'A') {
            $this->variantAViews++;
        } else {
            $this->variantBViews++;
        }
    }

    public function recordConversion(string $variant): void
    {
        if ($variant === 'A') {
            $this->variantAConversions++;
        } else {
            $this->variantBConversions++;
        }
    }

    public function getImprovement(): float
    {
        $rateA = $this->getVariantAConversionRate();
        $rateB = $this->getVariantBConversionRate();

        if ($rateA === 0) {
            return 0;
        }

        return (($rateB - $rateA) / $rateA) * 100;
    }

    public function determineWinner(): ?string
    {
        $rateA = $this->getVariantAConversionRate();
        $rateB = $this->getVariantBConversionRate();

        // Calculate statistical significance (simplified)
        $minSamples = max($this->variantAViews, $this->variantBViews);
        if ($minSamples < 100) {
            return null; // Not enough data
        }

        if ($rateA > $rateB * 1.1) { // 10% difference threshold
            return 'A';
        } elseif ($rateB > $rateA * 1.1) {
            return 'B';
        }

        return null;
    }
}
