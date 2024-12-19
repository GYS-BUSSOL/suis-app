import { fetchProcurement } from '@db/apps/procurement/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'


export const handlerAppsProcurement = [
    // Get Signature Type Details
    http.get(('/api/apps/procurement/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      let queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchProcurement(page, itemsPerPage, queryLower);
      const dataRows = fetchData.data.rows;
      console.log(dataRows)
      // Filter 
      let filteredProcurement = dataRows.filter(partner => (
        (
          partner.aplication_name?.toLowerCase().includes(queryLower) ||
          partner.visitor_name?.toLowerCase().includes(queryLower) ||
          partner.location?.toLowerCase().includes(queryLower)
        )
      )).reverse()

      // Sort Signature Type
      if (sortByLocal) {
        if (sortByLocal === 'aplication_name') {
          filteredProcurement = filteredProcurement.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.aplication_name.localeCompare(b.aplication_name)
            else
              return b.aplication_name.localeCompare(a.aplication_name)
          })
        }
      }
      const totalProcurement = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalProcurement / itemsPerPageLocal)
      console.log("filtered: ",filteredProcurement)
      
      return HttpResponse.json({
        procurement: filteredProcurement,
        totalPages,
        totalProcurement,
        page,
      }, { status: 200 })
    }),
]
