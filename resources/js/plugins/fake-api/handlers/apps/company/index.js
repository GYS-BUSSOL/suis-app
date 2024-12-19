import { addPPS, fetchPPSCompleted, fetchPPSOnGoing } from '@db/apps/pps/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsPPS = [
  
    // Get PPS Details
    http.get(('/api/apps/pps-ongoing/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const priority = url.searchParams.get('priority')
      const status = url.searchParams.get('status')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      const queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchPPSOnGoing(page, itemsPerPage, queryLower, priority, status);
      const dataRows = fetchData.data.rows;
      const totalPriorityCount = fetchData.data.total_priority || 0
      const totalNotPriorityCount = fetchData.data.total_not_priority || 0
      // Filter PPS
      let filteredPPS = dataRows.filter(pps => (
        (pps.con_req_no.toLowerCase().includes(queryLower) || 
        pps.con_bu.toLowerCase().includes(queryLower) || 
        pps.con_pps_no.toLowerCase().includes(queryLower) || 
        pps.join_second_description.toLowerCase().includes(queryLower) || 
        pps.con_cp_name.toLowerCase().includes(queryLower) || 
        pps.join_first_sts_description.toLowerCase().includes(queryLower) || 
        pps.con_priority_id.toLowerCase().includes(queryLower)) && 
        pps.con_priority_id === (priority || pps.con_priority_id) && 
        pps.join_first_sts_description === (status || pps.join_first_sts_description)
      )).reverse()

      // Sort PPS
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'con_priority_id') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_priority_id.localeCompare(b.con_priority_id)
            else
              return b.con_priority_id.localeCompare(a.con_priority_id)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalPPS = fetchData.data.total_record

      // total pages
      const totalPages = Math.ceil(totalPPS / itemsPerPageLocal)
      
      return HttpResponse.json({
        pps: filteredPPS,
        totalPages,
        totalPPS,
        totalPriorityCount,
        totalNotPriorityCount,
        page,
      }, { status: 200 })
    }),

    http.get(('/api/apps/pps-completed/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('qCompleted')
      const priority = url.searchParams.get('priority')
      const status = url.searchParams.get('status')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      const queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchPPSCompleted(page, itemsPerPage, queryLower, priority, status);
      const dataRows = fetchData.data.rows;
      const totalPriorityCount = fetchData.data.total_priority || 0
      const totalNotPriorityCount = fetchData.data.total_not_priority || 0
      // Filter PPS
      let filteredPPS = dataRows.filter(pps => (
        (pps.con_req_no.toLowerCase().includes(queryLower) || 
        pps.con_bu.toLowerCase().includes(queryLower) || 
        pps.con_pps_no.toLowerCase().includes(queryLower) || 
        pps.join_second_description.toLowerCase().includes(queryLower) || 
        pps.con_cp_name.toLowerCase().includes(queryLower) || 
        pps.join_first_sts_description.toLowerCase().includes(queryLower) || 
        pps.con_priority_id.toLowerCase().includes(queryLower)) && 
        pps.con_priority_id === (priority || pps.con_priority_id) && 
        pps.join_first_sts_description === (status || pps.join_first_sts_description)
      )).reverse()
      // Sort PPS Completed
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'con_priority_id') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_priority_id.localeCompare(b.con_priority_id)
            else
              return b.con_priority_id.localeCompare(a.con_priority_id)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredPPS = filteredPPS.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalPPSCompleted = fetchData.data.total_record

      // total pages
      const totalPages = Math.ceil(totalPPSCompleted / itemsPerPageLocal)
      
      return HttpResponse.json({
        ppsCompleted: filteredPPS,
        totalPages,
        totalPPSCompleted,
        totalPriorityCount,
        totalNotPriorityCount,
        page,
      }, { status: 200 })
    }),

    // Get Single pps Detail
    http.get(('/api/apps/pps/:id'), async ({ params }) => {
      const PPSId = Number(params.id)
      const pps = db.pps.find(e => e.id === PPSId)
      if (!pps) {
        return HttpResponse.json({ message: 'PPS not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...pps,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete PPS
    http.delete(('/api/apps/pps/:id'), async ({ params }) => {
      const PPSId = Number(params.id)
      const PPSIndex = db.pps.findIndex(e => e.id === PPSId)
      if (PPSIndex === -1) {
        return HttpResponse.json('PPS not found', { status: 404 })
      }
      else {
        db.pps.splice(PPSIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add PPS
    http.post(('/api/apps/pps/add'), async ({ request }) => {
      // Fetch Data
      const ppsFormData = await request.formData();
      console.log({ppsFormData});
      
      const ppsData = {};
      for (const [key, value] of ppsFormData.entries()) {
          ppsData[key] = value;
      }

      if (ppsData.shift_checklist) {
          ppsData.shift_checklist = JSON.parse(ppsData.shift_checklist);
      }
      if (ppsData.duration) {
          ppsData.duration = JSON.parse(ppsData.duration);
      }
      console.log({ppsData});
      
      const fetchData = await addPPS(ppsData);

      return HttpResponse.json({ body: fetchData }, { status: 201 })
    }),
]
