const userData = useCookie('userData').value.usr_access
console.log(userData)
let navbar;
if (userData === 'admin') {
  navbar = [
    { heading: 'Apps' },
    {
      title: 'Partner',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-partner' },
      ],
    },
    {
      title: 'Procurement',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-procurement' },
      ],
    },
    {
      title: 'Security',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-security' },
      ],
    },
    {
      title: 'Admin',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Location', to: 'apps-location' },
      ],
    }
  ]
} else if (userData === 'partner') {
  navbar = [
    { heading: 'Apps' },
    {
      title: 'Partner',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-partner' },
      ],
    },
  ]
} else if (userData === 'approvalpartner') {
  navbar = [
    { heading: 'Apps' },
    {
      title: 'Partner',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Approval SUIS', to: 'apps-barcode-partner-approval' },
      ],
    },
  ]
} else if (userData === 'ga') {
  navbar = [
    { heading: 'Apps' },
    {
      title: 'Security',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-security' },
      ],
    },
  ]
} else if (userData === 'proc') {
  navbar = [
    { heading: 'Apps' },
    {
      title: 'Procurement',
      icon: { icon: 'tabler-user' },
      children: [
        { title: 'Create Barcode', to: 'apps-barcode-procurement' },
      ],
    },
  ]
}
export default navbar
