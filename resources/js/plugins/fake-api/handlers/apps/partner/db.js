export async function fetchPartner(currentPage, rowPerPage, rowSearch) {
    let start = 0;
    const url = 'http://localhost:8000/api/apps/partner/search';
    const token = 'YOUR_BEARER_TOKEN_HERE';
    
    if(currentPage != 1 && currentPage > 1)
      start = (currentPage * rowPerPage) - rowPerPage
  
    const payload = {
      "paging": {
          "start": start,
          "length": rowPerPage
      },
      "select": [
        "id", "aplication_name", "location", "header_value", "exp_date", "start_date", "visitor_name", "del", "qr_image", "status", "barcode"
    ],
      "columns": [
        {
        "name": "aplication_name",
        "logic_operator": "=",
        "value": "Security Information System"
      },
      {
        "name": "header_value",
        "logic_operator": "like_front",
        "value": "24"
      },
      {
        "name": "del",
        "logic_operator": "isnull",
        "value": true
      },
      ],
      "group_column": {
        "operator": "AND",
        "group_operator": "OR",
        "where": [
          {
            "name": "id",
            "logic_operator": "like",
            "value": rowSearch
          },
          {
            "name": "aplication_name",
            "logic_operator": "like",
            "value": rowSearch
          },
          {
            "name": "visitor_name",
            "logic_operator": "like",
            "value": rowSearch
          },
          {
            "name": "location",
            "logic_operator": "like",
            "value": rowSearch
          },
        ]
      },
      "joins": [],
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
  