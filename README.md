# Build System

**/block-packages** - Contains the block packages that are available to the block manager service.  Each package is a directory that contains a package.json file and a blocks directory.  The blocks directory contains the block templates that are available to the block manager service.

**/components/blocks** - Building blocks for the block manager service.  This directory contains the block manager service, block manager, and block editor components.

**/components/blocks/config-panels** - Configuration panels for the block manager service.

**/components/css** - Contains the css for the block manager service.

**/services/blocks/type-service.ts** - register(BlockTypeDefinition)

**/types/block-types** - The block type definitions that will define the properties that a block has.

## Build Process
- Need
  - Builder / designer control
  - List of CSS controls, e.g. all, box {all,margin,padding,border}, font (family, size, unit) (could be builder controls)
  - List of Config Panels, including global or custom panels, e.g. general, advanced
- Typical .block files renders:
  - Block template [x] PHP [x] React
  - Config Panel? probably not since this can be generated based on block config
  - BlockTypeDefinition:
    ```mermaid
    classDiagram
      class BlockTypeDefinition~BlockType~ {
        string type
        BlockConfig~BlockType~ defaultConfig
        T defaultData
        FunctionComponent~BlockProps~BlockType~~ configPanel
        FunctionComponent~BlockProps~BlockType~~ designer
        string[] requiredParentTypes
        string[] supportedChildrenTypes 
      }
      class BlockConfig {
        CSSProperties css
      }
      class Block~BlockType~ {
        string type
        BlockConfig config
        T data
        Array~Block~BlockType~~ children
      }
      
      class CSSProperties {
      }
      BlockConfig "1" --> "n" CSSProperties
      BlockTypeDefinition ..> BlockConfig
```
How to manage conditional blocks?  Can probably be configurable based on structure / tree properties and value tree.

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