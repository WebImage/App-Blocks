# Templates
Blocks are a template language that allows the template to be transpiled into multiple formats.  The current focus is on PHP and React components.

A .block file can be used as a simple template, or they can contain special macros (e.g. @block and @controlDefinition) that affect how the template is transpiled.

## Macros

Macros are registered commands that can manipulate the output of a template or otherwise add meta data that might be helpful in the target language.

Macros are specified within a template file  start with the "at" sign followed by their name, e.g. @block.  Macro names are registered via the plugin interface.

## @block

You use the @block macro when you want the template to be transpiled into a template that can be used in the block manager service.  This allows the target template builder to retrieve a list of available "block" templates.

@block macros take two required parameters and one optional parameter.
1. name: string - This is the name that will be used to reference the block by type.  
2. class - The Camel-cased name that will be used to generate any required classes that are used to render the block.
3. label (optional) 
The first parameter is the name of the block.  The second parameter is the block type.  The optional parameter is the block description, or how it will be displayed to the end user.  If omitted then this block may not be available as a "publically" selected block type.

Adding @control('variable-name', 'control-name', 'User friendly label') to a block template allows the specified control to manage the value of the specified variable within the same template.
Adding @controlOption('variable-name', 'controlOptionName', 'value') Sets an option on the control specified by @control
Adding @property Will define a variable name, type, and default value for the block.

### @control
@control('variable-name', 'control-type', 'User friendly label') are used to specify that a variable within the block should be managed by the specified control

## @controlDefinition

The @controlDefinition macro registers any template code following its definition available as a control for use in blocks. 

## @controlOption

# Custom Language Support

The most common way to extend the block language is to create a custom macro, which has many of the requirements built in to manipulate the parsed template tree.

## Parsers

## Transpiling

Each target language will have a transpiler that converts the block file into the target language.  The transpiler will be responsible for parsing the block file and generating the target language