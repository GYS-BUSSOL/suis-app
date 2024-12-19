import { fetchPartner } from '@db/apps/partner/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'


export const handlerAppsPartner = [
    // Get Signature Type Details
    http.get(('/api/apps/partner/search'), async ({ request }) => {
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
      const fetchData = await fetchPartner(page, itemsPerPage, queryLower);
      const dataRows = fetchData.data.rows;
      console.log(dataRows)
      // Filter Signature Type
      let filteredPartner = dataRows.filter(partner => (
        (
          partner.aplication_name?.toLowerCase().includes(queryLower) ||
          partner.visitor_name?.toLowerCase().includes(queryLower) ||
          partner.location?.toLowerCase().includes(queryLower)
        )
      )).reverse()

      // Sort Signature Type
      if (sortByLocal) {
        if (sortByLocal === 'aplication_name') {
          filteredPartner = filteredPartner.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.aplication_name.localeCompare(b.aplication_name)
            else
              return b.aplication_name.localeCompare(a.aplication_name)
          })
        }
      }
      const totalPartner = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalPartner / itemsPerPageLocal)
      console.log("filtered: ",filteredPartner)
      
      return HttpResponse.json({
        partner: filteredPartner,
        totalPages,
        totalPartner,
        page,
      }, { status: 200 })
    }),
]
