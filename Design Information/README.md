# Summary

The goal in this project is to automate the synchronization of data across different apps where the formats aren't identical. Doing so requires assuming that the data in both ends relates to the same real-world task (billing, task-tracking, calendar) from where some meta-knowledge can be derived, and also that both systems have data they are interested in sharing.

The main idea for doing so consists of translating each different format into a common structure, which is used to compare the data stored in each database. From these versions it is possible to analyze what is currently available on each end, and with some additional logic this comparison results in a sequence of instructions (ADD, REMOVE, UPDATE) to be executed on each database.

# Requirements

1. Live-data sharing with simultaneous editing.
2. One-way and two-way synchronization.
3. Ensure privacy and sovereignty for all members.
4. Resilience to routing changes and failed deliveries.

# Description

## Part Description

1. **Authentication** - Grants CYB access to the user's data for synchronization purposes.

2. **Update Notifier** - Checks the available resources of applications being synched by CYB, to set up a separate notification system for each that will make requests either when a new entry is made (if webhooks are available) or periodically.

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

### One-Way Sync

Consider the image presented below as an example for one-way sync:

<img src="https://github.com/pondersource/CYB/blob/main/Design%20Information/one_way_sync.jpg?raw=true"/>

We are trying to syncronize data from system A to system B. Since we are not making assumptions about these systems to be syncronizing, our aplication must check to see resources it might have available to simplify the task. This diagram only considers "ADD" instructions but it can somewhat be generalized for other instructions.

The logic I'm trying to present goes like this: Do we care about copying data that was added to A and paste it in B? Yes! Do we have a webhook we can set-up to make system calls for CYB? If so, we can use these events for synchronization. If not, we need to periodically check for new entries. Are there changelogs available in system A? If so, we can periodically check the changelogs and generate instructions from there. If not we need to periodically query the API from system A. Is there any monotonic values in the API from system A we can use to reduce the amount of information asked in our queries? If not, then we need to periodically request all data from a given user and check for differences between the data in system A and system B.

In practice this logic will all be handled when setting up the update notifier instead of called for every "ADD" instruction, but I wanted to clarify this very common design pattern, where trying to generalize for the most systems can make the algorithm more and more convoluted. This is the level of generalization we are trying to achieve in this project.

If webhooks and changelogs aren't available in both systems A and B, then we need to periodically query both in order to generate instructions for system B, on a case to case basis:

    Synching from A to B
    
    case: Identical entry in system A and B:
        pass;

    case: Entry in system A but not system B:
        Add_to_B();

    case: Entry in system B but not system A:
        Remove_from_B();

    case: Identical entry with different optional fields in system A and B:
        case: system A doesn't have that optional field available:
            Pass;
        case: system A has a different value:
            Update_entry_B():
            
Let's consider as an example that for a specific day and user, system A has entry (1,1,1,04-01-2023,60,1000,), while system B has 2 entries (1,1,1,04-01-2023,60,0900,) and (1,1,1,04-01-2023,60,1500). According to the cases presented above, we would need to delete one entry in system B and update the other entry so it has the same value as in system A. So which one is deleted and which one is updated? It doesn't matter, as long as one gets deleted and one gets updated, both systems will have the same data.

### Two-way Sync

:construction: :construction: :construction:

## Note

To edit diagrams download a copy and open it in [here](https://app.diagrams.net/).
