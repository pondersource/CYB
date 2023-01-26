https://github.com/federatedbookkeeping/timesheets/wiki/Project-Write-up-and-Analysis#impacts-of-data-retransmission-by-intermediate-systems

Privacy – Message is forwarded between members in federation as a bitmask informing there is a message to read. Federated members with 1's can request to read the message and receive it back. Requests sent from 0's are ignored or denied or w/e. This requires a public list of members in a federation.

Quantification of the quality of a given connection between two members of a federation, as a measurement for the translation function as well as trust and amount of activity between them. Real-time variable.

The syncronized version of data is stored in a meta data file which can be held by all members of the federation (inspired by torrents). This file contains instructions on how to access and assemble the syncronized version of data, and allows all the members to also keep their data as local source of truth. Whether the result of the meta-file exists as an actual separate server or as an abstraction that can be called on demand by all members with permission is debatable. A public list of members would also allow the permissions for all the packages (data that can be arbitrarily fine-grained) to be directly handled in the meta-file.

Train neural networks to both configure the neighbourhoods and to minimize cost functions for multihop and ledger-loops. This allows all nodes to attempt to communicate with all other nodes, while the results of those communications is used to train the neural network and generate a neighbourhoods of paths it favors to use.

Our intention behind digital signatures is very similar to public/private keys in blockchain, it could even be implemented as giving every node an NFT collection and use pieces as signatures. list of members would allow these collections to also be known inside the federation. It could either be parsed as part of the meta file and used for validation of ownership for the packages transmitted, and/or used to validate the comunications between nodes (in this one I'm talking out of my ass so take it with a grain of salt).
