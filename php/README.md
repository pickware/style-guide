# PHP Naming Rules

## Interfaces
Do **NOT** include `Interface` in the class name.

Reasoning:
- There is no value in doing so.

## Abstract classes
Include `Abstract` in the class name.

Reasoning:
- Abstract classes need to be differentiable towards interfaces they implement.

## Services
Include `Service` in the class name.

Do **NOT** include service in the service name.

Reasoning:
- Service is created to be accessed using the DIC, implicating that it is a service

## Factories
Include `Factory` in the class name.

(Usually Factories are services, therefore their class name should end with `FactoryService`)

Reasoning:
- The name implies the factory pattern. 

## Name Blacklist
Please do not use these words in your classes
- Base
- Data
- Information
- Manager

## Additional remark
- A class name has to be logical even without its namespace

### Negative examples
- `class LabelManager`
- `Pickware\Stock\Manager`
- `$container->get('label_printing_service')`
- `class LabelRenderingContextFactory` *(if it's a service)*
