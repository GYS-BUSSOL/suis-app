import { fetchLocation } from '@db/apps/location/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsLocation = [
    // Get Signature Type Details
    http.get(('/api/apps/location/search'), async ({ request }) => {
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
      const fetchData = await fetchLocation(page, itemsPerPage, queryLower);
      const dataRows = fetchData.data.rows;
      // Filter Signature Type
      let filteredLocation = dataRows.filter(location => (
        (location.cr_db_id?.toLowerCase().includes(queryLower) ||
        location.join_first_cr_db_name?.toLowerCase().includes(queryLower))
      )).reverse()

      console.log("sortByLocal: ", sortByLocal)
      // Sort Signature Type
      if (sortByLocal) {
        if (sortByLocal === 'id') {
          filteredLocation = filteredLocation.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.id.localeCompare(b.id)
            else
              return b.id.localeCompare(a.id)
          })
        }
      }
      const totalLocation = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalLocation / itemsPerPageLocal)
      
      return HttpResponse.json({
        location: filteredLocation,
        totalPages,
        totalLocation,
        page,
      }, { status: 200 })
    }),
]
