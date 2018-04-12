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

# PHP Error Handling (RFC)

This addendum is, fow now, to be considered an RFC, i.e., a Request for
Comments. This is not final. Please comment. Please discuss specific issues
occuring during developing or reviewing regarding this topic in the pull
request.

Any error should be expressed with an exception.

We distinguish several kinds of errors and propose handling them distinctly.

## Error Distinction Dimension: **Exception's Origin Ownership**
An exception may be originally thrown in _our_ code, or in _third-party code_.

## Error Distinction Dimension: **Potential for Handling**
* _Non-recoverable errors_, or intent to crash: The code author throws an
  exception, which signals any further code execution …

    * … may endanger the integrity of execution flow. For example, the entire
      program's state is suspect and the current code is not likely to
      interpret all issues related to this error case correctly. This is
      especially important for functions with side effects, problems in
      which the caller might not expect or handle, e.g., if such side effects
      are not expected to be handled by the caller's domain realm.

    * … or, may be a very rare error case, which, in an ideal world, could
      hypothetically be handled, but that is not considered prudent or
      efficient to handle. For example, if handling an exception would involve
      creating a better PHP language runtime, rewriting an entire framework or
      handling memory bit flips caused by radiation. These may be cases, where
      it is more efficient to handle the fallout from such an error crashing
      a program and misbehaving, then creating a solution to the problem.

    * … or, error cases which are considered assertions of correctness, i.e.,
      cases which are not expected by the developer to be possible at all to
      occur during runtime, but are still checked for safety, e.g., to prevent
      worse outcomes or damage to systems if they were ignored.

  Such errors should almost always inherit from PHP SPL's
  [`LogicException`](http://php.net/LogicException) or one of its child
  classes. Do not ever attempt to _handle_ such an exception. If you think it
  could be handled, the exception should be rethought.

* _Recoverable errors_: The code author throws an exception, which the calling
  code is free to catch and process. There is no reason to assume the program
  cannot continue after catching the exception.  Exceptions for such cases
  thrown by our code should implement their own Exception class, inheriting
  from `\Exception` or a more general exception class of our own.  Never
  inherit such exceptions from third-party exceptions, `LogicException`, or
  `RuntimeException` unless you absolutely require third-party code to be able
  to handle this exception thrown by our code.

## Error Distinction Dimension: **Audience**

* _End User-level Errors_: Errors which are likely to be displayed to end users
  of the system. Such errors should implement
  [`LocalizableThrowable`](https://github.com/VIISON/ShopwareCommon/blob/master/Components/ExceptionTranslation/LocalizableThrowable.php)
  so that they can be translated for end users.

* All other errors: Errors which can only be understood by developers.

## Error Distinction dimension: Error Semantics

* _Business Error_: The exception occurs in the abstraction level of the
  current context and represents a valid state description. It is expected of
  the system to throw such exceptions and they represent non-happy-path issues
  which someone familiar with the business domain may understand. For Shopware
  plugins, this may mean that the owner of an online shop or its administrator
  can potentially understand the error. For a database abstraction layer, the
  respective domain would be database handling. Business Errors can always be
  handled by either code or end users of the domain.

* _Technical Error_: The exception occurs in the abstraction level of the
  current context, but occurs for technical reasons and do not represent
  issues with regards to a valid state of the business logic. For example,
  a necessary file not being readable may be such an error.

## Important Terminology for Error Handling

* _Throwing_ or _raising an exception_: `throw new $ExceptionClass(/* ... args */)`

* _Rethrowing an exception_: `try { /* code */ } catch (\SomeException $e) { throw $e; }`

* _Raising the abstraction level of an exception_: `try { /* code */ } catch (\SomeException $e) { throw \MyDomainException($e); }`

* _Handling an exception_: An exception is caught and the code reacts to the
  exception based on its semantics beyond rethrowing the exception in every
  case.

## Limitations for Error Handling Code
Currently, we have decided to support versions of PHP which lack finally, i.e.,
to support versions of PHP before PHP 5.5. Hence, please consider using our
[`TryWithFinally`](https://github.com/VIISON/ShopwareCommon/blob/master/Classes/TryWithFinally.php)
helper instead of recreating such code on your own. We offer two reasons for
this: This helper is heavily used and can therefore be assumed robust, and it
is easily greppable (findable) in our code base, so we can one day replace its
usage with actual `finally` clauses, once we remove support for PHP < 5.5.

