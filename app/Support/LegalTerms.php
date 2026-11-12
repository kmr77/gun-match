<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class LegalTerms
{
    public static function registrationVersion(): ?string
    {
        return self::publishedDocument('terms')['version'] ?? null;
    }

    /**
     * @return array{version: string, effective_at: string, sections: array<int, array{title: string, paragraphs: array<int, string>}>}|null
     */
    public static function publishedDocument(string $document): ?array
    {
        $configuration = config("gunmatch_legal.{$document}");
        if (! is_array($configuration)) {
            return null;
        }

        $version = $configuration['version'] ?? null;
        $effectiveAt = $configuration['effective_at'] ?? null;
        $sections = $configuration['sections'] ?? null;

        if (
            ($configuration['published'] ?? false) !== true
            || ! is_string($version)
            || preg_match('/\A[A-Za-z0-9._-]{1,50}\z/', $version) !== 1
            || ! is_string($effectiveAt)
            || trim($effectiveAt) === ''
            || CarbonImmutable::parse($effectiveAt)->isFuture()
            || ! self::hasPublishableSections($sections)
        ) {
            return null;
        }

        return [
            'version' => $version,
            'effective_at' => $effectiveAt,
            'sections' => $sections,
        ];
    }

    private static function hasPublishableSections(mixed $sections): bool
    {
        if (! is_array($sections) || $sections === []) {
            return false;
        }

        foreach ($sections as $section) {
            if (
                ! is_array($section)
                || ! is_string($section['title'] ?? null)
                || trim($section['title']) === ''
                || ! is_array($section['paragraphs'] ?? null)
                || $section['paragraphs'] === []
            ) {
                return false;
            }

            foreach ($section['paragraphs'] as $paragraph) {
                if (! is_string($paragraph) || trim($paragraph) === '') {
                    return false;
                }
            }
        }

        return true;
    }
}
