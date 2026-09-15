<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\DeliveryMatrix;

/**
 * Delivery matrix position letters.
 *
 * Positions run from "A" to "Z", followed by "AA" to "ZZ", so a two-letter
 * code always outranks a one-letter code and comparison is by length first.
 */
class Letter
{
    public const PATTERN = '/^[A-Z]{1,2}$/';

    /**
     * Normalize user input to the canonical uppercase form.
     *
     * @param string|null $value
     * @return string
     */
    public function normalize(?string $value): string
    {
        return strtoupper(trim((string)$value));
    }

    /**
     * Whether the value is a valid matrix position letter.
     *
     * @param string|null $value
     * @return bool
     */
    public function isValid(?string $value): bool
    {
        return $value !== null && preg_match(self::PATTERN, $value) === 1;
    }

    /**
     * Compare two matrix letters by their position in the matrix.
     *
     * @param string $a
     * @param string $b
     * @return int negative when $a comes before $b, positive when after, 0 when equal
     */
    public function compare(string $a, string $b): int
    {
        $byLength = strlen($a) <=> strlen($b);

        return $byLength !== 0 ? $byLength : strcmp($a, $b);
    }

    /**
     * Highest position among the given letters; null when none are valid.
     *
     * @param string[] $letters
     * @return string|null
     */
    public function highest(array $letters): ?string
    {
        $highest = null;
        foreach ($letters as $letter) {
            $letter = $this->normalize($letter);
            if (!$this->isValid($letter)) {
                continue;
            }
            if ($highest === null || $this->compare($letter, $highest) > 0) {
                $highest = $letter;
            }
        }

        return $highest;
    }
}
