# WrAPI

## Developer Notes

This guide is intended for developers who wish to extend wrAPI with additional handlers. Handlers can be implemented in
this extension or - the recommended way - in separate extensions. Basically you need the write the handler then register
it for wrAPI. After it will be available to select for routes.

### Implementing Handler

The handler should inherit from `CRM_Wrapi_Handler_Base`.

### Registering Handler

The following hook should be implemented:

**Definition**
`hook_civicrm_wrapiHandlers(&$handlers)`

**Parameters**

- `$handlers` - it contains the handlers
    ```
    $handler = [
      'handler_class_1' => 'handler name #1',
      'handler_class_2' => 'handler name #2',
    ];
    ```

**Example**

```php
function example_civicrm_wrapiHandlers(&$handlers)
{
    $handlers['CRM_Example_Handler_NewHandler']='Your new handler';
}
```
