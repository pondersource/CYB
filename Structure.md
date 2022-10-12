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
- The first time a person opens the CYB website, they have no session yet. Hence, they only see a list of supported applications with a login button for each one of them.
- The authentication process/requirements varies per application. One might require a redirect to an OAuth process or require a username/password type of deal.
- After the first application has been successfully authenticated, we create a user profile for this person and assign a session.
- A user profile record on our database is basically just an identifier that the authenticated applications point to. Indicating that these accounts belong to the same person.
- If someone without a session logs into an application that we have already created a user for, we have to recognize this and other than providing the session, update the UI to show all the logged in applications.
- If someone logs into application A in one browser and logs into application B in another browser and then logs into B from the first browser, we have to recognize this as well and merge the 2 user profiles that we have created. This can be addressed later!
- We need to maintain a set of authentication adopters. Each adopter would have a name, an icon, an authentication UI and the name of importer/exporter adopters they support for each data type.

## Synchronization
1. After an exporter has been switched on by the user, it either registers a webhook to receive events from the application or adds itself for that specific user to be notified in every time interval.
2. At specific webhooks or time intervals, an exporter adopter gets notified about some updates in the data.
3. The updates are sent in a specific format to every importer adopter for that user for that data type. The importer only writes this update as a pending operation in their respective database.
4. Then, each importer adopter with a pending operation is called sequentially to send their updates to their respectice applications. After completing each update, their remove it from their pending list.
5. Storing the update (step 3) and sending it to the application (step 4) is designed as a two step process to avoid failures or delays during the receiving process.
6. It might be a good idea to revive the importer adopters with pending tasks in a time interval basis to have a background retry going.
7. We do not import into an application if the exported source is the same application on the same user account.

## Unaddressed concerns
- The UX for when an application manages multiple types of data
- An strategy for handling the starting point of the sync
- What happens when access to an application is revoked without us knowing
- The case where a user has multiple accounts on the same application
