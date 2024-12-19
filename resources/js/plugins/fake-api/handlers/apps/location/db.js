export async function fetchLocation(currentPage, rowPerPage, rowSearch) {
    let start = 0;
    const url = 'http://localhost:8000/api/apps/location/search';
    const token = 'YOUR_BEARER_TOKEN_HERE';
    
    if(currentPage != 1 && currentPage > 1)
      start = (currentPage * rowPerPage) - rowPerPage
  
    const payload = {
      "paging": {
          "start": start,
          "length": rowPerPage
      },
      "columns": [],
      "group_column": {
        "operator": "AND",
        "group_operator": "OR",
        "where": [
          {
            "name": "loc_app",
            "logic_operator": "like",
            "value": rowSearch
          },
          {
            "name": "cr_db_name",
            "logic_operator": "like",
            "value": rowSearch,
            "table_name": "mst_db_codereadr"
          },
        ]
      },
      "joins": [
        {
          "name": "mst_db_codereadr",
          "column_join": "loc_db_codereadr",
          "column_results": ["cr_db_id", "cr_db_name"],
          "column_self": "cr_db_id"
        },
      ],
      "orders": {
        "columns": ["id"],
        "ascending": false
      }
    };
  
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          // 'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(payload)
      });
  
      if (!response.ok) {
        throw new Error(`Error: ${response.status}`);
      }
  
      const data = await response.json();
      return data;
  
    } catch (error) {
      console.error("Error fetching job type data:", error);
      throw error;
    }
  }
  