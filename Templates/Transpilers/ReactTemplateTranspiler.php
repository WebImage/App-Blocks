<?php

namespace WebImage\BlockManager\Templates\Transpilers;

use WebImage\BlockManager\Templates\Plugins\AuthorMacro;
use WebImage\BlockManager\Templates\Parsers\Branch;
use WebImage\BlockManager\Templates\Parsers\Plugins\EachMacroParser;
use WebImage\BlockManager\Templates\Parsers\Plugins\WrapMacroParser;
use WebImage\BlockManager\Templates\Parsers\TemplateParser;
use WebImage\BlockManager\Templates\Plugins\ControlOptionMacro;
use WebImage\BlockManager\Templates\Plugins\DraggableMacro;
use WebImage\BlockManager\Templates\Plugins\ExtendBlockMacro;
use WebImage\BlockManager\Templates\Plugins\JavascriptVariable;
use WebImage\BlockManager\Templates\Plugins\PropertyMacro;
use WebImage\BlockManager\Templates\Plugins\ReactCode;
use WebImage\BlockManager\Templates\Transpilers\Plugins\ControlMacroTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\IfSupportsMacroGroupTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\MacroGroupTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\ReactBlockMacroTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\ReactControlDefinitionTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\ReactEachMacroTranspiler;
use WebImage\BlockManager\Templates\Transpilers\Plugins\ReactHtmlTranspiler;

class ReactTemplateTranspiler extends Transpiler
{
    protected array $supportedFeatures = ['react'];

    public function __construct()
    {
        $this->plugin(new JavascriptVariable());
        $this->plugin(new ReactCode());
        $this->plugin(new MacroGroupTranspiler(TemplateParser::T_MACRO, [
            new ReactBlockMacroTranspiler(),
            new AuthorMacro(),
            new PropertyMacro(),
			new ReactControlDefinitionTranspiler(),
            new ControlMacroTranspiler(),
            new ControlOptionMacro(),
            new IfSupportsMacroGroupTranspiler(),
            new WrapMacroParser(),
            new ReactEachMacroTranspiler(),
            new ExtendBlockMacro(),
            new DraggableMacro(),
        ]));

        $this->plugin(new ReactHtmlTranspiler());
    }
}
