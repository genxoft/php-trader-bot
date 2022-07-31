<?php

namespace App\Trait;

trait CollectionTrait
{
    protected array $collection;
    abstract public static function itemsClassName(): string;

    /**
     * @param string $property
     * @return array
     */
    public function getColumn(string $property): array
    {
        if (!property_exists($this->itemsClassName(), $property)) {
            throw new \InvalidArgumentException(sprintf(
                "Property %s not found in %s",
                $property,
                $this->itemsClassName()
            ));
        }
        $result = [];
        foreach ($this->collection as $item) {
            $result[] = $item->$property;
        }
        return $result;
    }

    /**
     * @param string $property
     * @return static
     */
    public function indexBy(string $property): static
    {
        $new = clone($this);
        $newCollection = [];
        foreach ($new->collection as $item) {
            if (!property_exists($item, $property)) {
                throw new \InvalidArgumentException(sprintf(
                    "Property %s not found in %s",
                    $property,
                    get_class($item)
                ));
            }
            if (key_exists($item->$property, $newCollection)) {
                throw new \InvalidArgumentException(sprintf(
                    "Index conflict: value of property %s in not unique '%s'",
                    $property,
                    $item->$property
                ));
            }
            $newCollection[$item->$property] = $item;
        }
        return $new;
    }

    /**
     * @param string|null $property If null then it will order by array key. Key might be a numeric
     * @param int $direction SORT_ASC|SORT_DESC
     * @return static
     */
    public function orderBy(string $property = null, int $direction = SORT_ASC): static
    {
        $new = clone($this);
        if ($property === null) {
            switch ($direction) {
                case SORT_ASC:
                    ksort($new->collection, SORT_NUMERIC);
                    break;
                case SORT_DESC:
                    krsort($new->collection, SORT_NUMERIC);
                    break;
                default:
                    throw new \InvalidArgumentException("direction might be a SORT_ASC or SORT_DESC");
            }
            return $new;
        }
        if (!property_exists($this->itemsClassName(), $property)) {
            throw new \InvalidArgumentException(sprintf(
                "Property %s not found in %s",
                $property,
                $this->itemsClassName()
            ));
        }
        usort($new->collection, function (object $a, object $b) use ($property) {
            if (!is_numeric($a->$property) || !is_numeric($b->$property)) {
                throw new \InvalidArgumentException(sprintf(
                    "Value of property %s not is numeric",
                    $property
                ));
            }
            return $a->$property <=> $b->$property;
        });
        return $new;
    }
}
