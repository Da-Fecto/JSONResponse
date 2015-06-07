<?php

/**
 * JSON response with a “standardized” structure
 *
 * @author ©2015 Martijn Geerts
 *
 * ---------------------------------------------------------------------------------------
 *
 * 4 types: success, redirect, fail, error
 *
 * // 2xx Success
 * Success (All went well, and data was returned.)
 * - success (bool) true
 * - message (string) 'success'
 * - statusCode (integer) 200
 * - data (array) optional
 *
 * // 3xx Redirection
 * Redirect
 * No data is returned, only HTTP headers
 *
 * // 4xx Client Error
 * Fail (There was a problem with the submitted data or the API call wasn't satisfied)
 * - success (bool) false
 * - message (string) 'reason'
 * - statusCode (integer)
 * - errors (array) Humanreadable array of errors, informing the user.
 *
 * // 5xx Server Error
 * Error (An error occurred in processing the request, i.e. an exception was thrown)
 * - success (bool) false
 * - message (string) 'reason'
 * - statusCode (integer)
 * - errors (array) Humanreadable array of errors, informing the user.
 *
 * HTTP status codes
 * source: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 *
 * 200 OK
 * Standard response for successful HTTP requests. The actual response will depend on the
 * request method used. In a GET request, the response will contain an entity
 * corresponding to the requested resource. In a POST request, the response will contain
 * an entity describing or containing the result of the action.
 *
 * 201 Created
 * The request has been fulfilled and resulted in a new resource being created.
 *
 * 202 Accepted
 * The request has been accepted for processing, but the processing has not been
 * completed. The request might or might not eventually be acted upon, as it might be
 * disallowed when processing actually takes place.
 *
 * 204 No Content
 * The server successfully processed the request, but is not returning any content.
 * Usually used as a response to a successful delete request.
 *
 * 304 Not Modified
 * Indicates that the resource has not been modified since the version specified by the
 * request headers If-Modified-Since or If-None-Match. This means that there is no need to
 * retransmit the resource, since the client still has a previously-downloaded copy.
 *
 * 400 Bad Request
 * The server cannot or will not process the request due to something that is perceived to
 * be a client error (e.g., malformed request syntax, invalid request message framing, or
 * deceptive request routing).
 *
 * 401 Unauthorized
 * Similar to 403 Forbidden, but specifically for use when authentication is required and
 * has failed or has not yet been provided. The response must include a WWW-Authenticate
 * header field containing a challenge applicable to the requested resource.
 *
 * 403 Forbidden
 * The request was a valid request, but the server is refusing to respond to it. Unlike a
 * 401 Unauthorized response, authenticating will make no difference.
 *
 * 404 Not Found
 * The requested resource could not be found but may be available again in the future.
 * Subsequent requests by the client are permissible.
 *
 * 405 Method Not Allowed
 * A request was made of a resource using a request method not supported by that resource;
 * for example, using GET on a form which requires data to be presented via POST, or using
 * PUT on a read-only resource.
 *
 * 409 Conflict
 * Indicates that the request could not be processed because of conflict in the request,
 * such as an edit conflict in the case of multiple updates.
 *
 */
 class JSONResponse {

    /**
     * When true every attempt to set a property should return false.
     *
     * @var (mixed) NULL | true
     */
    protected $inputError = null;

    /**
     * Header statusCode & statusCode property
     *
     * @var (int)
     */
    protected $statusCode;

    /**
     * Header message
     *
     * @var (int)
     */
    protected $statusMessage;

    /**
     * On 2xx Success
     *
     * @var (bool)
     */
     protected $success = false;

    /**
     * Message
     *
     * @var (string)
     */
    protected $message;

    /**
     * Human readable errors
     *
     * @var (array)
     */
    protected $errors = array();

    /**
     * Returned data
     *
     * @var (array)
     */
     protected $data = null;

    /**
     * Used for headers
     *
     * @return (array) Staus codes
     *
     */
    protected $statusCodes = array(
        // 2xx Success
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '204' => 'No Content',
        // 3xx Redirection (No need to send data back)
        '304' => 'Not Modified',
        // 4xx Client Error
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '409' => 'Conflict',
        // 5xx Server Error
        '500' => 'Internal Server Error'
    );

   /**
     * Render returns the data & header to the client
     *
     * @return (string) JSON object
     *
     */
    public function render() {

        // No status code, don't know what to do
        if (!$this->statusCode && !count($this->errors) && $this->data === null) {
            $error  = "No properties set.";
            $this->setCodeAndMessage(400);
            $this->message = $this->statusMessage;
            $this->errors = array($error);
        }

        // No status code, don't know what to do
        if (!$this->statusCode) {
            $error  = "We could not determine a status code.";
            $this->setCodeAndMessage(409);
            $this->message = $this->statusMessage;
            $this->errors = array($error);
        }

        // Looks fine to me, set success
        if ($this->statusCode >= 200 && $this->statusCode < 300) {
            $this->success = true;
        }

        header('Content-Type: application/json');
        header("HTTP/1.1 " . strval($this->statusCode) . " " . $this->statusMessage);

        $o = array();
        $o['success'] = $this->success;

        // 2xx Success
        if ($this->statusCode < 300) {
            $o['statusCode'] = $this->statusCode;
            $o['message'] = $this->message;
            $o['data'] = $this->data;
        // Redirection (No need to send data back)
        } else if ($this->statusCode >= 300 && $this->statusCode < 400) {
            $o['statusCode'] = $this->statusCode;
            $o['message'] = $this->message;
        // 4xx Client Error
        } else if ($this->statusCode >= 400 && $this->statusCode < 500) {
            $o['statusCode'] = $this->statusCode;
            $o['message'] = $this->message;
            $o['errors'] = $this->errors;
        // 5xx Server Error
        } else if ($this->statusCode >= 500) {
            $o['statusCode'] = $this->statusCode;
            $o['message'] = $this->message;
            $o['errors'] = $this->errors;
        }

        return json_encode($o, JSON_PRETTY_PRINT);
    }

    /**
     * Setup defaults
     *
     * @var $array (array) Data or error. dependent on $code
     * @var $code (integer) HTTP status code
     */

    public function __construct($array = array(), $code = 200) {

        // Is it an array and has data in it? Else NULL
        $isArray = is_array($array) && count($array) ? $array : null;
        $code = ctype_digit("$code") && ((int) $code) ? (int) $code : null;

        // Redirection need no data
        if (is_int($array)) {
            // make isArray true-ish
            $isArray = 'used-as-code';
            $code = (int) $array;
            $array = null;
        }

        // No properties set
        if ($isArray === null && $code === 200) {
            return $this;
        }

        // Nothing constructed
        if ((!$isArray || !$code) && $code < 400 && $code > 500) {
            return $this;
        }

        // Only dropped data in the array 200 assumed
        if ($code < 300) {
            $this->data = $array;
        // 3xx Redirection (No need to reply)
        } else if ($code >= 400 && $code < 500 ) {
            $this->errors[] = $array;
        // 5xx Server Error
        } else if (is_array($array) && $code >= 500) {
            $this->errors[] = $array;
        // No need for data or array
        } else {
            // Don't know what to do ('Method Not Allowed')
            $code = 405;
            $this->inputError = true;
            $this->errors[] = get_class($this) . "::construct doesn't know what to do!";
        }

        $this->setCodeAndMessage($code);
    }

    /**
     * Sets new header and executes setCodeAndMessage().
     *
     * @var $code (integer)
     *
     */
    protected function setHeader($value) {
        $value = (string) $value;
        if (strlen($value) < 5) return;
        preg_match('/^(\d+) (.+)$/', $value, $matches);
        $code = (int) $matches[1];
        $message = (string) $matches[2];
        if (is_int($code) && $code >= 100 && $code < 600) {
            $this->statusCodes[strval($code)] = $message;
            $this->setCodeAndMessage($code);
            return true;
        }

        $error = array('The format for header is not recognized.');
        $this->statusCode = 405;
        $this->statusMessage = (string) $this->statusCodes['405'];
        $this->message = 'Invalid header';
        $this->errors = $error;
        $this->inputError = true;
        return false;
    }

    /**
     * Sets statusMessage and message if not already set.
     *
     * @var $code (integer)
     * @return (bool)
     *
     */
    protected function setCodeAndMessage($code) {

        $code = (int) $code;
        $statusCodes = (array) $this->statusCodes;
        $codeString = (string) $code;
        if (isset($statusCodes[$codeString])) {
            $this->statusCode = (int) $code;
            $this->statusMessage = (string) $statusCodes[$codeString];
            $this->message = $this->message ? $this->message : $this->statusMessage;
            return true;
        } else {
            $error = 'Status code ' . $codeString . ' not implemented in ' . get_class($this);
            $this->statusCode = 405;
            $this->statusMessage = (string) $statusCodes['405'];
            $this->errors[] = $error;
            $this->inputError = true;
            return false;
        }
    }

    /**
     * Sets error or data.
     *
     * @var $property (string) name of the property, errors or data
     * @var $value (array),
     * @return (bool)
     *
     */
    protected function setDataOrErrors($property, $value) {

        if (!is_array($value)) {
            return false;
        } else if ($property === 'data') {
            $otherProperty = 'errors';
            $this->setCodeAndMessage(200);
        } else if ($property === 'errors') {
            $otherProperty = 'data';
            $this->setCodeAndMessage(400);
        }

        $count = count($this->$otherProperty);

        if ($count) {
            //Contradictory send of data & errors
            $error = "Sending " . $property . " when " . $otherProperty . " already exists is contradictory.";
            $this->setCodeAndMessage(409);
            $this->message = $this->statusMessage;
            $this->errors = array($error);
            $this->inputError = true;
            return false;
        }

        return true;
    }

    /**
     * Setter for the properties
     *
     * @var $property (string) name of the property to set
     * @var $value (mixed) value with the type according to the property
     */
    public function set($property, $value) {

        // Not allowed to set when there's an inputError
        if ($this->inputError) return false;

        if (!in_array($property, array('statusCode', 'message', 'data', 'errors', 'header'))) {
            $this->inputError = true;
            $error = "Property '" . $property . "' does not exist or is not callable in this context.";
            $this->setCodeAndMessage(400);
            $this->errors[] = $error;
            return false;
        }

        switch ($property) {
            case 'statusCode':
                $expected = 'integer';
                $isValid = is_int($value);
                if ($isValid) $this->setCodeAndMessage($value);
                break;
            case 'message':
                $expected = 'string';
                $isValid = is_string($value);
                break;
            case 'errors':
                $expected = 'array';
                $isValid = $this->setDataOrErrors($property, $value);
                break;
            case 'data':
                $expected = 'array';
                $isValid = $this->setDataOrErrors($property, $value);
                break;
            case 'header':
                if (!$this->setHeader($value)) return;
                $isValid = true;
                break;
            default:
                break;
        }

        if (!$isValid && !$this->inputError) {
            $error = $expected . " expected for property '" . $property . "' " . gettype($value) . " given.";
            $this->setCodeAndMessage(400);
            $this->errors[] = $error;
            $this->inputError = true;
        }

        if ($isValid) $this->$property = $value;
    }
}
