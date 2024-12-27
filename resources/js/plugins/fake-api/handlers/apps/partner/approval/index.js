import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'
import { fetchPartnerApproval } from './db'


export const handlerAppsPartnerApproval = [
    http.get(('/api/apps/partner/approval'), async ({ request }) => {
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
      const fetchData = await fetchPartnerApproval(page, itemsPerPage, queryLower);
      const dataRows = fetchData.data.rows;
      // Filter Signature Type
      let filteredPartner = dataRows.filter(partner => (
        (
          partner.visitor_name?.toLowerCase().includes(queryLower) ||
          partner.purpose?.toLowerCase().includes(queryLower) ||
          partner.location?.toLowerCase().includes(queryLower)
        )
      )).reverse()

      // Sort Signature Type
      if (sortByLocal) {
        if (sortByLocal === 'id') {
          filteredPartner = filteredPartner.sort((a, b) => {
            if (orderByLocal === 'desc')
              return a.id.localeCompare(b.id)
            else
              return b.id.localeCompare(a.id)
          })
        }
      }
      const totalPartner = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalPartner / itemsPerPageLocal)
      
      return HttpResponse.json({
        partner: filteredPartner,
        totalPages,
        totalPartner,
        page,
      }, { status: 200 })
    }),
]
