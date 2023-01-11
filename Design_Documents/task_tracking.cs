Union:
{
    Customer_Id (required)
    Project_Id (required)
    Worker_Id (required)
    Date (required)
    Duration (required)
    
    From (optional)
    System_A_Record_Id (optional)
    System_B_Record_Id (optional)
    (...)
}

Reader:
{
    Format;
    Filter[];
    last_sync[start,end];
    biggest_id;
    
    Query(Filter);
    List_all_Queries(Filter);

}

Synching from A to B 

case: Entry in system A but not system B:
    Add_to_B;

case: Entry in system B but not system A:
    Remove_from_B;

case: Identical entry in system A and B:
    pass;

case: different optionals entry in system A and B:
    Update_missing_fields;

case: System A has entries 1,1,1,04-01-2023,60,, and 1,1,1,04-01-2023,60,,
System B has entries 1,1,1,04-01-2023,60,0900, and 1,1,1,04-01-2023,60,1500:
If system A entry is '': pass;
If system A entry is value: make "From" in System B = value;

case: System A has entries 1,1,1,04-01-2023,60,1000, and 1,1,1,04-01-2023,60,1100,
System B has entries 1,1,1,04-01-2023,60,0900, and 1,1,1,04-01-2023,60,1500:
If system A entry is '': pass;
If system A entry is a value: make "From" in System B = value;

case: System A has entries 1,1,1,04-01-2023,60,1000,
System B has entries 1,1,1,04-01-2023,60,0900, and 1,1,1,04-01-2023,60,1500:
Update 1 entry from System B and delete the other;

Reader Questions:

Do we care about add in system A
Is there a webhook?
Are there changelogs in system A?
Do we care about updates/removes to existing records in system A?
Do we care about add/update/remove in system B?
Are identifiers monotonic?
Relationship between record_date and query_date?
