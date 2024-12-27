<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';
import { VForm } from 'vuetify/components/VForm';

definePage({
  meta: {
    layout: 'blank',
    public: true,
    unauthenticatedOnly: true,
  },
})
const route = useRoute()
const router = useRouter()
const ability = useAbility()

const credentials = ref({
  username: '',
  password: '',
  captcha: '',
  remember: false,
})
const refVForm = ref()
const captchaImage = ref('')
const isPasswordVisible = ref(false)
const errors = ref({
  username: undefined,
  password: undefined,
})
const loadingBtn = ref([])

const reloadCaptcha = async () => {
  try {
    const response = await $api(`/reload-captcha`, {
      method: 'GET',
    });
    const rows = response
    console.log(rows)
    
  } catch (error) {
    console.error('Error load captcha');
  }
}

const login = async () => {
  try {
    loadingBtn.value[0] = true
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        username: credentials.value.username,
        password: credentials.value.password,
        captcha: credentials.value.captcha,
      },
      onResponseError({ response }) {
        loadingBtn.value[0] = false
        console.log(JSON.stringify(response));
        errors.value = response._data.errors
      },
    });
    const { accessToken, userData, userAbilityRules, status } = res
    if (status == 201) {
  
      useCookie('userAbilityRules').value = userAbilityRules
      ability.update(userAbilityRules)
      useCookie('userData').value = userData
      useCookie('accessToken').value = accessToken
      await nextTick(() => {
        router.replace(route.query.to ? String(route.query.to) : '/')
      })
    }
  } catch (err) {
    loadingBtn.value[0] = false
    throw new Error("Failed login");
  }
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid)
      login()
  })
}

</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/login">
              <div class="">
                <VNodeRenderer :nodes="themeConfig.app.logoLogin" />
                <h5 class="app-logo-title">
                  <!-- {{ themeConfig.app.title }} -->
                </h5>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            SUIS
          </h4>
          <p class="mb-0">
            Please sign-in to your account
          </p>
        </VCardText>

        <VCardText>
          <VForm 
            ref="refVForm"
            @submit.prevent="onSubmit">
            <VRow>
              <!-- username -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.username"
                  placeholder="Username"
                  autofocus
                  label="Username"
                  type="text"
                  :rules="[requiredValidator]"
                  :error-messages="errors.username"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :rules="[requiredValidator]"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              <div v-html="captchaImage" class="captcha-image"></div>
              <button @click="reloadCaptcha">Reload</button>
              
              <!-- captcha -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.captcha"
                  placeholder="Captcha"
                  autofocus
                  label="Captcha"
                  type="text"
                  :rules="[requiredValidator]"
                  :error-messages="errors.captcha"
                />
              </VCol>

                <!-- remember me checkbox -->
                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="credentials.remember"
                    label="Remember me"
                  />

                </div>

                <!-- login button -->
                <VBtn
                  block
                  type="submit"
                  :loading="loadingBtn[0]"
                  :disabled="loadingBtn[0]"
                >
                  Login
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
