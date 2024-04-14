<?php

namespace WebImage\BlockManager\src\Templates\Transpilers;

use WebImage\BlockManager\src\Templates\Context;
use WebImage\BlockManager\src\Templates\Meta;
use WebImage\BlockManager\src\Templates\Parsers\Branch;

interface TranspilerInterface
{
    const META_SUPPORT_FEATURES = '_supportedFeatures';

//    public function transpile(Branch $branch, Meta $meta = null, ContextManager $context = null): string;

    public function transpile(Branch $branch, Meta $meta = null, Context $context = null): string;

    /**
     * @param array $branches
     * @return string
     */
    public function transpileBranches(array $branches): string;

    public function plugin(TranspilerPluginInterface $plugin): void;

    /**
     * @return TranspilerPluginInterface[]
     */
    public function plugins(): array;

    public function indent(string $text, int $depth=1): string;
}
