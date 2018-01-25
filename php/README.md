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
Include the prefix `Abstract` in the class name.

Reasoning:
- Abstract classes need to be distinguishable from interfaces they implement.

E.g.:

- `abstract class AbstractRenderingContext`

**NOT**:
- `abstract class TestCase`
- `abstract class Struct`

## Services
Suffix the class name with `Service`.

Do **NOT** include `service` in the service name in the Dependency Injection Container.

Reasoning:
- Service is created to be accessed using the Dependency Injection Container, implying that it is a service

E.g.:
- `class DocumentRenderingService`
- `class LabelPrintingService`

**NOT**:
- `class Auth`
- `class Translation`

## Factories
Include the suffix `Factory` in the class name.

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
Please do not use these words in your classes since they are too generic and/or meaningless:
- Base
- Data
- Information
- Manager

## Additional remark
- A class name has to be understandable as well as distinguishable even without its namespace
