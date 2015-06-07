# JSONResponse

PHP JSONResponse is build for the recurring need to send “standardized” structured JSON back. This way we can anticipate on the returned data. Standardized doesn't means an informal convention, but a way to prevent re-inventing the wheel when sending JSON response back.


## Using the constructor

StatusCode of 200 will be returned on a standard successful response.

```php
// php (returning data)
$response = new JSONResponse(array('key' => 'value'));
echo $response->render();

```

```javascript
// Returned JSON
{
    "success": true,
    "statusCode": 200,
    "message": "OK",
    "data": {
        "key": "value"
    }
}
```

For an error response you need to set an additional statusCode.

```php
// php (returning errors)
$response = new JSONResponse(array('key' => 'value'), 405);
echo $response->render();
```
```javascript
// Returned JSON
{
    "success": false,
    "statusCode": 405,
    "message": "Method Not Allowed",
    "errors": [
        {
            "key": "value"
        }
    ]
}
```

## Using the properties

For a more fine-grained control you can use the setters below. 

- **data** (array) Array containing data
- **statusCode** (integer) 
	- Available: 200, 201, 204, 304, 400, 401, 403, 404, 405, 409 & 500
	- When **not** available use i.e.: $instance->set('header', '210 gone')
- **message** (string)
- **errors** (array) Array containing errors
- **header** (string) Valid PHP header

#### Set properties

```php
/**
 * Setting properties.
 *
 */
$response = new JSONResponse(array('key' => 'value'));
// When data is set, automatically a 200 status code & 200 header is send.
$response->set('data', array('key' => 'value'));
// Overrule the 'default' statusCode & header.
$response->set('statusCode', 200);
// Set a message, when not set text from the header is used. (typically not needed to set) 
$response->set('message', 'No tralala, but oei oei.');
// Set errors, automatically a 400 status code & 400 header is send.
$response->set('errors', array('error text 1', 'error text 2', 'etc.'));
// Set a header not available in the class.
$response->set('header', '200 Ok');
// Echo all data to the client.
echo $response->render();

```
