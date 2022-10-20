<?php

interface AuthenticationAdopter {

    public function getAppCodeName();
    public function getName();
    public function getIconURL();
    public function getSupportedDataTypes();
    public function getReadersForDataType($data_type);
    public function getWritersForDataType($data_type);
    public function getAuthenticationUI($session);

}

interface Authentication {

    public function getAppCodeName();
    public function getDisplayName();
    public function getUserId();
    public function checkLiveness();

}

function getAuthenticationAdopters() {
    // Read adopters from the database or hard coded list
}

function onNewAuthentication($session, $appCodeName, $remote_user_id, $authentication_info) {
    // Create new user if required
    // If authentication with this user already exists, return
    // Create an authentication object and store it in the database
    // Merge with an existing user if required
    // Return the authentication
}

interface ReadAdopter {

    public function getAppCodeName();
    public function getDataType();
    public function getCodeName();
    public function setup($authentication);
    public function shutDown($authentication);

}

abstract class Read {

    abstract public function getAppCodeName();
    abstract public function getDataType();
    abstract public function getAdopterCodeName();
    abstract public function getUserId();

    public function extractData() {
        // Get the Authentication object for this user
        // Extract the new data from the application and return it or return null
    }

    public function Read($data) {
        // Get the list of Writs for the current user for this data type
        // On each Writ call storeUpdate with the data to be Writed
        // On each Writ call sync
    }

}

function setupReader($authentication_adopter, $data_type, $authentication) {
    // Get Reader for data type from authentication adopter
    // Call setup on Reader and get Read object
    // Store Read on the database and return it
}

function shutDownReader($authentication_adopter, $data_type, $authentication) {
    // Get Reader for data type from authentication adopter
    // Call shutdown on Reader and get Read object
    // Delete Read from the database and return it
}

interface WriteAdopter {

    public function getAppCodeName();
    public function getDataType();
    public function getCodeName();
    public function setup($authentication);
    public function shutDown($authentication);

}

abstract class Write {

    abstract public function getAppCodeName();
    abstract public function getDataType();
    abstract public function getAdopterCodeName();
    abstract public function getUserId();

    public function storeUpdate($update) {
        // Store this update object on the pending updates table for this user
    }

    public function sync() {
        // Get the list of updates from the database
        // Get the Authentication object for this user
        // Send each update into the application
    }

}

function setupWriter($authentication_adopter, $data_type, $authentication) {
    // Get Writer for data type from authentication adopter
    // Call setup on Writer and get Write object
    // Store Write on the database and return it
}

function shutDownWriter($authentication_adopter, $data_type, $authentication) {
    // Get Writer for data type from authentication adopter
    // Call shutdown on Writer and get Write object
    // Delete Write from the database and return it
}

?>