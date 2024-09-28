<?php
    /**
     * This script demonstrates how to use the Freemius SDK to deploy (publish a release) on Freemius.
     * 
     * Important Notes:
     * - The script initializes the Freemius API using developer credentials.
     * - It uploads a plugin (as a .zip file) and deploys a new version of the plugin to Freemius.
     * - After deploying, the script generates download URLs for both the free and paid versions of the plugin.
     * - This is just a demonstration and should be adjusted for a production environment.
     * 
     * Usage:
     * - Replace 'FS__API_DEV_ID', 'FS__API_PUBLIC_KEY', and 'FS__API_SECRET_KEY' with actual developer credentials.
     * - The 'file' parameter in the deployment section should point to the actual plugin zip file.
     * - Ensure you handle API errors and responses in real-world scenarios, as this demo skips error checking.
     * - The 'file_put_contents()' section shows how you might download the paid version of the plugin to the local filesystem.
     * 
     * Disclaimer:
     * - This script is intended for demonstration purposes only and should not be used as-is in a production environment.
     * - Sensitive information such as API keys should be stored securely and not hardcoded directly into your scripts.
     */

    require_once './vendor/autoload.php';

    use OldFreemius\Freemius_Api;

    define( 'FS__API_SCOPE', 'developer' );  // API scope is set to 'developer' for accessing developer endpoints
    define( 'FS__API_DEV_ID', 1234 );  // Replace this with your actual Freemius Developer ID
    define( 'FS__API_PUBLIC_KEY', 'pk_YOUR_PUBLIC_KEY' );  // Replace with your actual public key
    define( 'FS__API_SECRET_KEY', 'sk_YOUR_SECRET_KEY' );  // Replace with your actual secret key

    // Initialize the Freemius SDK with the developer credentials
    $api = new Freemius_Api(FS__API_SCOPE, FS__API_DEV_ID, FS__API_PUBLIC_KEY, FS__API_SECRET_KEY);

    // Deploy a new version of the plugin by uploading a zip file.
    // This is a POST request to the 'tags' endpoint for plugin versioning.
    $tag = $api->Api('plugins/115/tags.json', 'POST', array(
        'add_contributor' => true // Add contributor information to the plugin release
    ), array(
        'file' => './my-plugin.zip'  // Path to the plugin's zip file
    ));

    // Generate signed (secure) download URLs for both the free and paid versions of the plugin.
    $free_version_download_url = $api->GetSignedUrl( "/plugins/{$tag->plugin_id}/tags/{$tag->id}.zip?is_premium=false" );
    $paid_version_download_url = $api->GetSignedUrl( "/plugins/{$tag->plugin_id}/tags/{$tag->id}.zip?is_premium=true" );

    // Example: Download the paid version of the plugin to a local file.
    if ( file_put_contents($local_file_path, file_get_contents($paid_version_download_url)) ) {
        // If the download was successful, the file will be saved locally.
        // You can perform additional actions here such as logging or notification.
    }

    // Output the tag details (version info) returned from the API.
    print_r($tag);
