# CYB structure

## Purpose
Connect Your Books wants to create an application level internet. The first types of data that we want to connect are time tracking, invoicing and issue tracking data.

The goal is to avoid becoming yet another connector that people will have to pay to use. Meaning the software will be opensource and free. We try to make our sense presence as minimum as possible. Almost no web pages. No signup. Only connections.

## Overview
The goal is to allow individuals to have their data synced between different softwares that they are using. We avoid playing the role of privacy or access management. This is considered the concern of respective applications that the person is using.

To start connecting their books, a person opens our website. On the website there is a list of applications that we support. Each with a login button and after logging in, there are 2 options (if supported) to either take data out of or into the software or both.

No authentication is required. After the first application login, we automatically create a user id and authenticate them in our service.

For each application that we support, we create exporter and importer adopters. When an exporter announces that new data is available, the importers come online and act on it. These adopters are developed as semi-independet services.

In case an application like JIRA supports multiple types of data, we will probably have to add them in our UI and let the user select. This aspect is not addressed in this document.

There is also the matter of sync start point in time. Do we sync everything that happens after the application authentication or do we try to sync all of the data or some thing in the middle? It can even be yet another setting that is disposed to the individual to decide.

## Authentication

