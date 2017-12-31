# static-overloads
Use parameter-varying method overloads in PHP

Pass an array of functions with different signatures into the `Overload` constructor. Invoke the `Overload` object directly with an array of the parameters you got, and the library will determine the correct implementation to use based on the parameter types.

Example:

```php
function my_func(...$args){
    // We are storing the Overload object as a static-scope variable.
    // This caches the analyzed implementation signatures to improve runtime performance
    // if the function is called frequently within the same runtime.
    static $overload = null; // due to PHP language limitations, $overload cannot be initialized inline.
    $overload = $overload ?? new Overload([
        function(string $a){
            implementation1($a);
        },
        function(int $a){
            implementation2($a);
        }
    ]);
```
