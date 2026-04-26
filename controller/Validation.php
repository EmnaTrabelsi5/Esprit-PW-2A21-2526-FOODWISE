<?php

declare(strict_types=1);

namespace Controller;

/**
 * Validation côté serveur (aucune délégation aux attributs HTML).
 */
final class Validation
{
    public static function trim(?string $value): string
    {
        return trim((string) $value);
    }

    public static function requireNonEmpty(string $value, string $label, array &$errors): void
    {
        if ($value === '') {
            $errors[] = 'Le champ « ' . $label . ' » est obligatoire.';
        }
    }

    public static function maxLength(string $value, int $max, string $label, array &$errors): void
    {
        if (mb_strlen($value) > $max) {
            $errors[] = 'Le champ « ' . $label . ' » ne doit pas dépasser ' . $max . ' caractères.';
        }
    }

    public static function optionalInt(?string $raw, string $label, int $min, int $max, array &$errors): ?int
    {
        $raw = self::trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        if (!ctype_digit($raw)) {
            $errors[] = 'Le champ « ' . $label . ' » doit être un entier positif.';
            return null;
        }
        $n = (int) $raw;
        if ($n < $min || $n > $max) {
            $errors[] = 'Le champ « ' . $label . ' » doit être compris entre ' . $min . ' et ' . $max . '.';
            return null;
        }
        return $n;
    }

    public static function requireInt(?string $raw, string $label, int $min, int $max, array &$errors): ?int
    {
        $raw = self::trim((string) $raw);
        self::requireNonEmpty($raw, $label, $errors);
        if ($raw === '') {
            return null;
        }
        if (!ctype_digit($raw)) {
            $errors[] = 'Le champ « ' . $label . ' » doit être un entier positif.';
            return null;
        }
        $n = (int) $raw;
        if ($n < $min || $n > $max) {
            $errors[] = 'Le champ « ' . $label . ' » doit être compris entre ' . $min . ' et ' . $max . '.';
            return null;
        }
        return $n;
    }

    /** Format attendu : AAAA-MM-JJ */
    public static function requireDate(?string $raw, string $label, array &$errors): ?string
    {
        $raw = self::trim((string) $raw);
        self::requireNonEmpty($raw, $label, $errors);
        if ($raw === '') {
            return null;
        }
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if ($dt === false || $dt->format('Y-m-d') !== $raw) {
            $errors[] = 'Le champ « ' . $label . ' » doit être une date valide (AAAA-MM-JJ).';
            return null;
        }
        return $raw;
    }

    public static function requireOneOf(string $value, array $allowed, string $label, array &$errors): void
    {
        if (!in_array($value, $allowed, true)) {
            $errors[] = 'La valeur du champ « ' . $label . ' » n\'est pas autorisée.';
        }
    }

    public static function dateOrder(?string $start, ?string $end, array &$errors): void
    {
        if ($start === null || $end === null || $start === '' || $end === '') {
            return;
        }
        if ($start > $end) {
            $errors[] = 'La date de début doit précéder ou égaler la date de fin.';
        }
    }
}
