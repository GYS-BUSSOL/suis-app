import { setupWorker } from 'msw/browser'

// Handlers
import { handlerAppBarSearch } from '@db/app-bar-search/index'
import { handlerAppsAcademy } from '@db/apps/academy/index'
import { handlerAppsCalendar } from '@db/apps/calendar/index'
import { handlerAppsChat } from '@db/apps/chat/index'
import { handlerAppsEcommerce } from '@db/apps/ecommerce/index'
import { handlerAppsEmail } from '@db/apps/email/index'
import { handlerAppsInvoice } from '@db/apps/invoice/index'
import { handlerAppsKanban } from '@db/apps/kanban/index'
import { handlerAppsLocation } from '@db/apps/location/index'
import { handlerAppLogistics } from '@db/apps/logistics/index'
import { handlerAppsPartnerApproval } from '@db/apps/partner/approval'
import { handlerAppsPartner } from '@db/apps/partner/index'
import { handlerAppsProcurement } from '@db/apps/procurement/index'
import { handlerAppsSecurity } from '@db/apps/security/index'
import { handlerAppsUsers } from '@db/apps/users/index'
import { handlerDashboard } from '@db/dashboard/index'
import { handlerPagesDatatable } from '@db/pages/datatable/index'
import { handlerPagesFaq } from '@db/pages/faq/index'
import { handlerPagesHelpCenter } from '@db/pages/help-center/index'
import { handlerPagesProfile } from '@db/pages/profile/index'

const worker = setupWorker(
  ...handlerAppsPartnerApproval,
  ...handlerAppsPartner, 
  ...handlerAppsProcurement, 
  ...handlerAppsSecurity, 
  ...handlerAppsLocation, 
  ...handlerAppsEcommerce, 
  ...handlerAppsAcademy, 
  ...handlerAppsInvoice, 
  ...handlerAppsUsers, 
  ...handlerAppsEmail, 
  ...handlerAppsCalendar, 
  ...handlerAppsChat, 
  ...handlerPagesHelpCenter, 
  ...handlerPagesProfile, 
  ...handlerPagesFaq, 
  ...handlerPagesDatatable, 
  ...handlerAppBarSearch, 
  ...handlerAppLogistics, 
  ...handlerAppsKanban, 
  ...handlerDashboard
)
export default function () {
  const workerUrl = `${import.meta.env.BASE_URL.replace(/build\/$/g, '') ?? '/'}mockServiceWorker.js`
  
  worker.start({
    serviceWorker: {
      url: workerUrl,
    },
    onUnhandledRequest: 'bypass',
  })
}
