Task tracking with live data

Tasks:

1.Talk with Meld and Wikisweets
2.“We don't want to keep a copy of all the data in every application. That will probably be infeasible in practice.”	
3.“It would be an awesome solution if multiple instances of CYB or other software solutions can work in parallel. Meaning we don't rely on historic data in general. Because some other software might also be busy performing some form of sync on the same data. So we don't assume that it is only a single instance of our software and the user who is manipulating the data.”
4.Multi-homed data which means it needs to be forwarded twice.
5.Investigate routing protocols for “user can use more than one node, which means the routing table can't be hardcoded”
6.Investigate BlockChain for authentication and verification of users; implications for digital signatures. 
7.Investigate Google Sheets for live data editing

Main issues:

1.access control - trust, privacy, editing and format conversion
2.version control (data conflicts) - add, delete and update according to a generalized accepted truth with conflict solving with guarantee of node convergences, its pretty straightforward for 1-way-sync but gets messy on a 2-way-sync where both sides have sovereignty.
3.open source tools only
4.offline support

Research questions (These were answered in presentation and article):

1.How do I decide to accept data?
2.Who do I forward?
3.How do I settle conflict?
4.How do I translate the data?

Rabbit Hole questions (These are open):

1.How are neighbours picked and updated?
2.What are the channels?
3.How is trust initialized and updated?
4.How are versions handled?
5.Which node has to translate the data? (sender/receiver)
6.How does one identify the same user across diferent platforms?
7.Are nodes aware of other members besides their neigbours?
8.Maybe connectedness can work around a lack of agility? 

Misc:

1.tigerbeetle;
2.self-federated-bookkeping;
3.prejournal bookkeping: allows changing between bird eye record (3rd person) and corporate record (1st person); Uses many 1st person perspectives to build a 3rd person map.
