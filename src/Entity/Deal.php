<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="deals",
 *     indexes={
 *         @ORM\Index(name="deals_name_idx", fields={"name"})
 *     }
 * )
 */
class Deal
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    public int $id;

    /**
     * @ORM\Column(type="string", length=256, nullable=false)
     */
    public string $name;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    public string $symbol;

    /**
     * @ORM\Column(type="string", length=8, nullable=false)
     */
    public string $side;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    public string $type;

    /**
     * @ORM\Column(type="bigint", options={"unsigned":true}, nullable=false)
     */
    public int $orderId;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    public string $clientOrderId;

    /**
     * @ORM\Column(type="bigint", options={"unsigned":true}, nullable=false)
     */
    public int $transactTime;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=10, nullable=false)
     */
    public string $price;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=10, nullable=false)
     */
    public string $origQty;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=10, nullable=false)
     */
    public string $executedQty;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=10, nullable=false)
     */
    public string $cummulativeQuoteQty;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    public string $status;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=10, nullable=true)
     */
    public ?string $profit = null;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $property => $value) {
            if (!property_exists($this, $property)) {
                continue;
            }
            $this->$property = $value;
        }
    }
}
