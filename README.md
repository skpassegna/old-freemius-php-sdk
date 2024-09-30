# Freemius PHP SDK


This SDK provides a PHP interface for interacting with the Freemius API from your own server-side applications or scripts. It handles authentication, request signing, and response parsing, enabling you to access and manage your Freemius account data, user information, plugin/theme installations, licenses, payments, and more – all from outside of your WordPress plugin or theme.

**Note:** A new, improved SDK is currently under development (by @skpassegna).

This SDK is particularly useful for building custom dashboards, reporting tools, integration with other services, or performing administrative tasks related to your Freemius products. It is *not* intended for use within your WordPress plugins or themes themselves; that's the role of the Freemius WordPress SDK.


## Installation

Use Composer to install the SDK:

```bash
composer require skpassegna/old-freemius-php-sdk
```

## Usage

Here's a basic example demonstrating how to retrieve your plugin's information:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Freemius\Freemius;
use Freemius\Exceptions\Freemius_Exception;

// Your Freemius credentials (obtain these from your Freemius dashboard)
$scope = 'plugin'; // Use 'developer' if working across multiple plugins, 'install' for a single installation, or 'user' for user-related actions.
$id = YOUR_PLUGIN_ID;  // Replace with your plugin/developer/install/user ID, depending on the scope.
$publicKey = 'pk_YOUR_PUBLIC_KEY'; // Replace with your public key
$secretKey = 'sk_YOUR_SECRET_KEY'; // Replace with your secret key
$isSandbox = false; // Set to true for sandbox mode

$fs = new Freemius($scope, $id, $publicKey, $secretKey, $isSandbox);

try {
    if ($fs->Test()) { // Test Connectivity to Freemius API
        echo "Freemius connection successful!\n";
        $plugin = $fs->Api('/.json'); // Retrieves current plugin's information (requires 'plugin' scope).
        print_r($plugin);


    } else {
        echo "Freemius connection failed!\n";
        // Troubleshooting: Clock Synchronization
        // The Freemius API uses timestamps for security.  If your server's clock is out of sync, 
        // you may encounter errors.  The following code helps detect and correct clock differences.
        $clockDiff = $fs->FindClockDiff();
        Freemius::SetClockDiff($clockDiff);
        if ($fs->Test()) {
            echo "Connection successful after clock sync.\n";
        } else {
            echo "Connection still failed. Check your credentials and ensure your server can reach the Freemius API.\n";
        }
    }


} catch (Freemius_Exception $e) {
    echo "Freemius API Error: " . $e->getMessage() . " (Code: " . $e->getStringCode() . ")\n";
    // Handle the Freemius-specific exception, potentially logging the error or displaying a user-friendly message.
    // You can access more details about the error using $e->getResult().
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Handle other exceptions
}
```

## Authentication

The SDK handles authentication automatically. You provide your public and secret keys during initialization.  The SDK uses a signature-based authentication method, generating a unique signature for each API request to ensure security.

## Scopes

The `$scope` parameter is critical and determines the context of your API interactions. It specifies which Freemius entity the SDK will operate on and dictates which API endpoints are accessible.  Here's a summary of common scopes:

| Scope       | Description                                                     | ID Used                     |
|-------------|-----------------------------------------------------------------|------------------------------|
| `plugin`    | Access data for a specific plugin.                             | Plugin ID                    |
| `developer` | Access data across all plugins under your developer account.    | Developer ID                  |
| `install`   | Access data for a single plugin installation on a website.      | Installation ID               |
| `user`      | Access data related to a specific Freemius user.                 | User ID                      |
| `app`       | Access app level data | App ID |



## API Calls

The `Api()` method is the primary way to interact with the Freemius API:

```php
$response = $fs->Api('/endpoint.json', 'GET', $params, $fileParams); 
```

* **`$endpoint`**: The API endpoint path relative to the base path determined by the scope.  For example, `/plugins.json` (to list plugins under a developer account – developer scope), `/.json` (to get the current plugin's information - plugin scope), or  `/installs.json` (to list installs of a plugin - plugin scope).
* **`$method`**: The HTTP method (e.g., `'GET'`, `'POST'`, `'PUT'`, `'DELETE'`). Defaults to 'GET'.
* **`$params`**: An associative array of parameters for the request (e.g., query parameters or JSON data for POST requests).  For multipart requests (file uploads), include any extra JSON data under the 'data' key as a JSON encoded string.
* **`$fileParams`**: An associative array for multipart form uploads (e.g. plugin deployments). The key is the field name (e.g., 'file'), and the value is the file path.


The `Api()` method returns the decoded JSON response from the Freemius API or the raw response if decoding fails.  The Freemius API primarily uses JSON for data exchange.


## Signed URLs

Generate secure, time-limited URLs for accessing protected resources:

```php
$signedUrl = $fs->GetSignedUrl('/endpoint.json?param1=value1'); // Include query parameters as needed
```

Signed URLs are useful for scenarios where you need to provide direct access to a resource without sharing your secret key.



## Sandbox Mode

Test your integration without affecting live data by enabling sandbox mode:

```php
$fs = new Freemius($scope, $id, $publicKey, $secretKey, true); // true enables sandbox mode
```

Make sure to use your sandbox API credentials when testing in sandbox mode.


## Error Handling

The SDK may throw exceptions during API interactions.  Proper error handling is essential:

```php
try {
    $response = $fs->Api('/endpoint.json');
// ... (other example code as before)
```



## Key API Endpoints and Examples

Here's a summary with examples to help you get started:

**Plugin Scope:**

* Get Plugin Details: `$fs->Api('/.json')`
* Get Latest Version Info: `$fs->Api('/updates/latest.json')`
* Deploy New Version (Simple): `$fs->Api('/plugins/YOUR_PLUGIN_ID/tags.json', 'POST', [], ['file' => './my-plugin.zip'])`
* Deploy New Version (With Additional Data): `$fs->Api('/plugins/YOUR_PLUGIN_ID/tags.json', 'POST', ['data' => json_encode(['add_contributor' => true])], ['file' => './my-plugin.zip'])`
* Deploy New Version (With Release Mode): `$fs->Api('/plugins/YOUR_PLUGIN_ID/tags.json', 'POST', ['data' => json_encode(['release_mode' => 'released'])], ['file' => './my-plugin.zip'])`


**Developer Scope:**

* Get all Plugins: `$fs->Api('/plugins.json')`
* Get all Installs of a Plugin: `$fs->Api('/plugins/{plugin_id}/installs.json')`  (Replace `{plugin_id}` with the actual ID)
* Create a Developer: $fs->Api('/apps/YOUR_APP_ID/developers.json', 'POST', $developerData);
* Get Developer's billing information: $fs->Api('/developers/YOUR_DEV_ID/billing.json', 'GET');
* Get Developer's balance: $fs->Api('/apps/YOUR_APP_ID/developers/YOUR_DEV_ID/balance.json', 'GET');


**Install Scope:**

* Get Install Details: `$fs->Api('/.json')`
* Start a Trial (for a plan): `$fs->Api('/plans/{plan_id}/trials.json', 'POST')`(Replace `{plan_id}` with the actual ID)
* Deactivate an Install's License: `$fs->Api('/licenses/{license_id}.json', 'DELETE')` (Replace `{license_id}` with the actual ID)
* Retrieve an install's uninstall details :  `$fs->Api('/uninstall.json', 'GET')`
* Get install's plan:  `$fs->Api('/plans.json', 'GET')`


**User Scope:**

* Get User Details: `$fs->Api('/.json')`
* Get User's Installs of a Plugin: `$fs->Api('/plugins/{plugin_id}/installs.json')` (Replace `{plugin_id}` with the actual ID)
* Sync User with Freemius: `$fs->Api('/plugins/{YOUR_PLUGIN_ID}/users/{user_id}.json', 'PUT', $userData)`


Remember to replace placeholders like `YOUR_PLUGIN_ID`, `YOUR_APP_ID`,`YOUR_DEV_ID`, `pk_YOUR_PUBLIC_KEY`, and `sk_YOUR_SECRET_KEY` with your actual values.  Consult the Freemius API documentation for details on request parameters and response formats.


## License

GPL-2.0+