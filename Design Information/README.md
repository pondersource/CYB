# Summary

The goal in this project is to automate the syncronization of data across different apps where the formats aren't identical. Doing so requires assuming that the data in both ends relates to the same real-world task (billing, task-tracking, calendar), from where some meta-knowledge can be derived.

The main idea for doing so consists of translating each different format into a common structure, which is used to compare the data stored in each database. From these versions it is possible to analyze what is currently available on each end, and with some additional logic this comparison results in a sequence of instructions (ADD, REMOVE, UPDATE) to be executed on each database.

# Requirements

1. Live-data sharing with simultaneous editing.
2. One-way and two-way synchronization.
3. Ensure privacy and sovereignty for all members.
4. Resilience to routing changes and failed deliveries.

# Description

## Part Description

1. **Authentication** - Grants CYB access to the user's data for syncronization purposes.

2. **Update Notifier** - Checks the available resources of the application being synced to set up a notification system that will make requests either when a new entry is made (if webhooks are available) or periodically.

4. **Reader** - Makes query requests for a particular format in a particular application. Is also capable of filtering, making lists and store information about the last request made.

5. **Writer** - Makes new entries for a particular format in a particular application, according to a sequence of instructions.

6. **Change Interpreter** - Compares the information presented by the readers of different applications and generates a sequence of instructions for the writers, which will sync the data on both ends. 

## Format Description

The formats used by the readers and writers contain the list of data fields which are available of each database, and two-way translation functions to handle conversions from and into the common structure used for comparison in the change interpreter.

This common structure uses the meta-knowledge on the real-world task being handled to assertain a short list of data fields which are mandatory and a much longer list of optional data fields, which can be used to simplify translation functions.

Important to note than in order to perform translation without ambiguities or loss of information, it is necessary to keep track of the formats used for translating a message.

## Structure Description

This part is covered [here](https://github.com/pondersource/CYB/blob/main/Design%20Information/Structure.md)

## Logic Description



### Note

To edit the diagrams download a copy and open it in [here](https://app.diagrams.net/).
