# CYB structure

## Purpose
Connect Your Books wants to create an application level internet. The first types of data that we want to connect are time tracking, invoicing and issue tracking data.

The goal is to avoid becoming yet another connector that people will have to pay to use. Meaning the software will be opensource and free. We try to make our sense presence as minimum as possible. Almost no web pages. No signup. Only connections.

## Overview
<img src="https://github.com/pondersource/CYB/blob/main/Design%20Information/concept.png?raw=true"/>

The goal is to allow individuals to have their data synced between different softwares that they are using. We avoid playing the role of privacy or access management. This is considered the concern of respective applications that the person is using.

To start connecting their books, a person opens our website. On the website there is a list of applications that we support. Each with a login button and after logging in, there are 2 options (if supported) to either take data out of or into the software or both.

No authentication is required. After the first application login, we automatically create a user id and authenticate them in our service.

For each application that we support, we create read and write adapters. These adapters are developed as semi-independet services.

There is also the matter of sync start point in time. Do we sync everything that happens after the application authentication or do we try to sync all of the data or some thing in the middle? It can even be yet another setting that is disposed to the individual to decide.

## Authentication
- The first time a person opens the CYB website, they have no session yet. Hence, they only see a list of supported applications with a login button for each one of them.
- The authentication process/requirements varies per application. One might require a redirect to an OAuth process or require a username/password type of deal.
- After the first application has been successfully authenticated, we create a user profile for this person and assign a session.
- A user profile record on our database is basically just an identifier that the authenticated applications point to. Indicating that these accounts belong to the same person.
- If someone without a session logs into an application that we have already created a user for, we have to recognize this and other than providing the session, update the UI to show all the logged in applications.
- If someone logs into application A in one browser and logs into application B in another browser and then logs into B from the first browser, we have to recognize this as well and merge the 2 user profiles that we have created. This can be addressed later!
- We need to maintain a set of authentication adapters. Each adapter would have a name, an icon, and an authentication UI.

## Synchronization
- For each data type and application combination, we have an update notifier, a reader and a writer implementation
- After reading gets switched on, the update notifier is asked to register a hook and notify on updates
- After receiving a notification from an update notifier, sync tasks will be created in the sync queue per each write that has been switched on by the user
- If a sync task with the same source and destination already exists, we do not create a new sync task
- A task queue is processed on each update notification for the newly created tasks
- After each task is processed, it is removed from the queue
- On a constant time interval we make an attempt to process all the remaining tasks as well
- For each data type, we have a default change interpreter that compares the data on the source reader with the data on the destination reader and produces a change report
- The writer then receives the change report and applies it to the destination application
- We can have custom implementations of change interpreter based on its variables
- We never create a sync task from an application to itself!

## Addressed concerns
### Complex applications
An application might support different data sources and data types. The authentication adopter is tasked to point to a specific data source within the application and then tell us which data types are available in this data source. Under each application there are multiple authentications. And under each authentication there are multiple data types. Each data type has a read and a write switch that the user can turn on or off.

## Unaddressed concerns
- An strategy for handling the starting point of the sync
- What happens when access to an application is revoked without us knowing
