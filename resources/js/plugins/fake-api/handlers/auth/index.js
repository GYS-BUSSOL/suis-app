// import { login } from '@db/auth/db'
// import { HttpResponse, http } from 'msw'

// // Handlers for auth
// export const handlerAuth = [
//   http.post(('/api/auth/login'), async ({ request }) => {
//     const { username, password } = await request.json()
//     const fetchLogin = await login(username,password)
//     const dataResponse = JSON.parse(JSON.stringify(fetchLogin))

//     if (dataResponse.status == 201) {
//       const data = fetchLogin.data
//       const token = fetchLogin.token
      
//       const response = {
//         status: 201,
//         userAbilityRules: [
//           {
//             action: 'manage',
//             subject: 'all',
//           },
//         ],
//         accessToken : token,
//         userData: data,
//       }
//       return HttpResponse.json(response, { status: 201 })
//     } else {
//       const errors = { username: ['Invalid username or password'] }
//       return HttpResponse.json({ errors }, { status: 400 })
//     }
//   }),
// ]
