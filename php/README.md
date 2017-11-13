# PHP Naming Rules

## Interfaces
Do **NOT** include `Interface` in the class name.

Reasoning:
- There is no value in doing so.

E.g.:

- `interface RenderingEngine`
- `interface RenderingContext`

**NOT**:
- `interface ValidatorInterface`


## Abstract classes
Include `Abstract` in the class name.

Reasoning:
- Abstract classes need to be differentiable towards interfaces they implement.

E.g.:

- `abstract class AbstractRenderingContext`

**NOT**:
- `abstract class TestCase`
- `abstract class Struct`

## Services
Include `Service` in the class name.

Do **NOT** include service in the service name.

Reasoning:
- Service is created to be accessed using the DIC, implicating that it is a service

E.g.:
- `class DocumentRenderingService`
- `class LabelPrintingService`

**NOT**:
- `class Auth`
- `class Translation`

## Factories
Include `Factory` in the class name.

(Usually Factories are services, therefore their class name should end with `FactoryService`)

Reasoning:
- The name implies the factory pattern.

E.g.:
- `class DocumentRenderingContextFactoryService`
 
**NOT**:
- `class InvoiceCreator`

## Structs
Do **NOT** include `Struct` in class name.

Reasoning:
- There is no value in doing so.

E.g.:
- `class Emotion`
- `class PaperSize`

**NOT**:
- `class ShopStruct`
- `class CategoryStruct`

## Name Blacklist
Please do not use these words in your classes
- Base
- Data
- Information
- Manager

## Additional remark
- A class name has to be logical even without its namespace
