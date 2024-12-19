// export const db = {
//   userTokens: [
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MX0.fhc3wykrAnRpcKApKhXiahxaOe8PSHatad31NuIZ0Zg',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6Mn0.cat2xMrZLn0FwicdGtZNzL7ifDTAKWB0k1RurSWjdnw',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6M30.PGOfMaZA_T9W05vMj5FYXG5d47soSPJD1WuxeUfw4L4',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6NH0.d_9aq2tpeA9-qpqO0X4AmW6gU2UpWkXwc04UJYFWiZE',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6NX0.ocO77FbjOSU1-JQ_BilEZq2G_M8bCiB10KYqtfkv1ss',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6Nn0.YgQILRqZy8oefhTZgJJfiEzLmhxQT_Bd2510OvrrwB8',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6N30.KH9RmOWIYv_HONxajg7xBIJXHEUvSdcBygFtS2if8Jk',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OH0.shrp-oMHkVAkiMkv_aIvSx3k6Jk-X7TrH5UeufChz_g',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OX0.9JD1MR3ZkwHzhl4mOHH6lGG8hOVNZqDNH6UkFzjCqSE',
//     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTB9.txWLuN4QT5PqTtgHmlOiNerIu5Do51PpYOiZutkyXYg',
//   ],
//   users: [
//     {
//       id: 1,
//       fullName: 'Wahyu Nugraha',
//       username: 'wahyu.nugraha',
//       password: '@WHYgys2024',
//       avatar: `${import.meta.env.BASE_URL.replace(/build\/$/g, '') ?? '/'}images/avatars/avatar-1.png`,
//       email: 'wahyu.nugraha@gyssteel.com',
//       role: 'admin',
//       abilityRules: [
//         {
//           action: 'manage',
//           subject: 'all',
//         },
//       ],
//     },
//     {
//       id: 2,
//       fullName: 'Jane Doe',
//       username: 'janedoe',
//       password: 'client',
//       avatar: `${import.meta.env.BASE_URL.replace(/build\/$/g, '') ?? '/'}images/avatars/avatar-2.png`,
//       email: 'client@demo.com',
//       role: 'client',
//       abilityRules: [
//         {
//           action: 'read',
//           subject: 'AclDemo',
//         },
//       ],
//     },
//   ],
// }

// export async function login(username, password)
// {
//   const url = 'http://localhost:8000/api/auth/login';
//   const payload = {
//     username,
//     password
//   }

//   try {
//     const response = await fetch(url, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify(payload)
//     });

//     if (!response.ok) {
//       throw new Error(`Error: ${response.status}`);
//     }

//     const data = await response.json();
//     return data;

//   } catch (error) {
//     return "Error fetching login";
//   }
// }
