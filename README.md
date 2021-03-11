

This project is a middle man API server that allows RESTfull API calls to be used to communicate with ERP.


## Design Choices

All controllers extend BaseAPIController.

BaseAPIController has a method called `defaultExecution`. This method allows for new end points to be quickly added with minimal effort as it handles:
  - Checking the cache.
  - Validation (with the help of a Validation class).
  - Communicating with ERP (with the help of a Model class).
  - Saving ERP's response to cache.
  - Displaying response to user.

Brief logic overview:
  - Ensure the JSON Web Token is present and valid.
  - Check the cache, if anyone has made a similar call recently, return the cached data.
  - Run a validation check.
  - Convert response to an array.
  - Save converted response to cache.
  - Display converted response to user.

## Namespace Breakdown
```
| Namespace               | Purpose                                     |
|:-----------------------:|:-------------------------------------------:|
| App\Validation          | Assist with validation                      |
| App\Traits              | Contain small repetative logic              |
| App\Services            | Wrapper around other classes                |
| App\Models\Kerridge     | Instructions for fine tuning API end points |                          |
| App\Exceptions          | custom exceptions                           |
```


## Credits

Titan is built upon the [*CodeIgnitier4*](https://codeigniter4.github.io/CodeIgniter4/index.html) framework.
