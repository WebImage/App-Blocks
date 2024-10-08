<?php

namespace WebImage\Blocks\Templates\Parsers;

use WebImage\Blocks\Templates\Meta;

class RequirementHelper
{
    const META_FULFILLS = 'requirements.fulfills';
    const META_REQUIRES = 'requirements.requires';
    public static function fulfillsRequirement(Meta $meta, string $requirement)
    {
        $meta[self::META_FULFILLS] = isset($meta[self::META_FULFILLS]) ? array_merge($meta[self::META_FULFILLS], [$requirement]) : [$requirement];
    }
    public static function addRequirement(Meta $meta, string $requirement) {
        $meta[self::META_REQUIRES] = isset($meta[self::META_REQUIRES]) ? array_merge($meta[self::META_REQUIRES], [$requirement]) : [$requirement];
    }

    public static function getFulfillments(Meta $meta): array
    {
        return $meta[self::META_FULFILLS] ?? [];
    }

    private static function getRequirements(): array
    {
        return $meta[self::META_REQUIRES] ?? [];
    }
}
