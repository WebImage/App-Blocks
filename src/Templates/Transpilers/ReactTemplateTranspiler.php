<?php

namespace WebImage\Blocks\Templates\Transpilers;

use WebImage\Blocks\Templates\Parsers\Plugins\WrapMacroParser;
use WebImage\Blocks\Templates\Parsers\TemplateParser;
use WebImage\Blocks\Templates\Plugins\AuthorMacro;
use WebImage\Blocks\Templates\Plugins\ControlOptionMacro;
use WebImage\Blocks\Templates\Plugins\DraggableMacro;
use WebImage\Blocks\Templates\Plugins\ExtendBlockMacro;
use WebImage\Blocks\Templates\Plugins\JavascriptVariable;
use WebImage\Blocks\Templates\Plugins\PropertyMacro;
use WebImage\Blocks\Templates\Plugins\ReactCode;
use WebImage\Blocks\Templates\Transpilers\Plugins\ControlMacroTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\IfSupportsMacroGroupTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\MacroGroupTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\ReactBlockMacroTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\ReactControlDefinitionTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\ReactEachMacroTranspiler;
use WebImage\Blocks\Templates\Transpilers\Plugins\ReactHtmlTranspiler;

class ReactTemplateTranspiler extends Transpiler
{
    protected array $supportedFeatures = ['react'];

    public function __construct()
    {
        $this->initPlugins();
    }

	private function initPlugins()
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
