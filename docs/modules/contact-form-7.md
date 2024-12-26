# Contact Form 7

:::tip
You must enable this module from the [Wordpress Settings](/inertia-wordpress/settings) menu to receive data
:::

Contact Form 7 is a simple, lightweight plugin for handling the creation, validation and
processing of user-submitted content on your Wordpress application.

## Module Features

- Provides all registered forms to your frontend
- Shares reCaptcha data when enabled
- Disables CF7 JavaScript and CSS stylesheets
- Automatically handles validation errors with support for the InertiaJS form helper
- Extension for Inertia form helper for all frameworks
- Messages will be flashed to the `$page.props.flash.cf7` namespace

## useForm Extended Composable

Inertia Wordpress provides an extended version of the `useForm` helper that automatically extracts and passes your form fields, sets up the correct submission URL and handles populating the `errors` that your controller might return.

::: code-group

```vue [Vue]
<script>
import { useCf7Form } from "@evo-mark/inertia-wordpress/vue";

const { form, formRef, fields, cf7Form } = useCf7Form("contact-form-1");
</script>

<template>
  <form ref="formRef" @submit.prevent="form.submit">
    <template v-for="field in fields">
      <input v-model="form[field]" :placeholder="field" /><br />
      <span>{{ form.errors[field] }}</span
      ><br />
    </template>
    <button type="submit">Submit</button>
    <span>{{ form.errors.recaptcha }}</span>
  </form>
</template>
```

```jsx [React]
import { useCf7Form } from "@evo-mark/inertia-wordpress/react";

const Component = () => {
  const { data, setData, submit, errors, fields, formRef } =
    useCf7Form("contact-form-1");

  return (
    <form
      ref={formRef}
      onSubmit={(ev) => {
        ev.preventDefault();
        submit();
      }}
    >
      {fields.map((field) => (
        <div key={field}>
          <input
            value={data[field]}
            placeholder={field}
            onChange={($event) => setData(field, $event.target.value)}
          />
          <br />
          <span>{errors[field]}</span>
        </div>
      ))}
      <button>Submit</button>
      <span>{errors.recaptcha}</span>
    </form>
  );
};

export default Component;
```

```js [Svelte]
<script>
    import { useCf7Form } from "@evo-mark/inertia-wordpress/svelte";

    let { form, fields, formRef, cf7Form } = useCf7Form("contact-form-1");
</script>

<form
    use:formRef
    onsubmit={(ev) => {
        ev.preventDefault();
        $form.submit();
    }}
>
    {#each fields as field}
        <input
            type="text"
            bind:value={$form[field]}
            placeholder={field}
        /><br />
        <span>{$form.errors[field]}</span><br />
    {/each}
    <button type="submit">Submit</button>
</form>
```

:::

If you're using the Google reCaptcha integration, any spam errors will be made available on `errors.recaptcha`.

## Return Object

While there are some variations between the frameworks (see examples above), there are some common objects/functions that are returned when using the extended helper.

### form

The main form reactive object. This is the same as if you'd called `useForm` directly.

### errors (React only)

An object containing any validation errors. On the other frameworks, this is on the `form` object.

### formRef

A template reference that is used in tandem with the reCaptcha integration to only load the extra scripts when the form is visible in the viewport. If you choose not to use the formRef, you will need to call `loadCaptcha` and `unloadCaptcha` manually.

### fields

A flat array of fields that are present in your form.

### cf7Form

The full Contact Form 7 object for your form. You will need to use this if you need a deeper integration between CF7's form settings and your frontend.

An example CF7 form object might be:

```json
{
  "id": 22,
  "name": "contact-form-1",
  "title": "Contact form 1",
  "locale": "en_GB",
  "fields": [
    {
      "name": "your-name",
      "labels": [],
      "type": "text",
      "required": true,
      "content": "",
      "options": ["autocomplete:name"],
      "values": [],
      "attr": ""
    },
    {
      "name": "your-email",
      "labels": [],
      "type": "email",
      "required": true,
      "content": "",
      "options": ["autocomplete:email"],
      "values": [],
      "attr": ""
    },
    {
      "name": "your-subject",
      "labels": [],
      "type": "text",
      "required": true,
      "content": "",
      "options": [],
      "values": [],
      "attr": ""
    },
    {
      "name": "your-message",
      "labels": ["testing"],
      "type": "textarea",
      "required": false,
      "content": "",
      "options": [],
      "values": ["testing"],
      "attr": ""
    },
    {
      "name": "",
      "labels": ["Submit"],
      "type": "submit",
      "required": false,
      "content": "",
      "options": [],
      "values": ["Submit"],
      "attr": ""
    }
  ],
  "messages": {
    "mail_sent_ok": "Thank you for your message. It has been sent.",
    "mail_sent_ng": "There was an error trying to send your message. Please try again later.",
    "validation_error": "One or more fields have an error. Please check and try again.",
    "spam": "There was an error trying to send your message. Please try again later.",
    "accept_terms": "You must accept the terms and conditions before sending your message.",
    "invalid_required": "Please fill out this field.",
    "invalid_too_long": "This field has a too long input.",
    "invalid_too_short": "This field has a too short input.",
    "upload_failed": "There was an unknown error uploading the file.",
    "upload_file_type_invalid": "You are not allowed to upload files of this type.",
    "upload_file_too_large": "The uploaded file is too large.",
    "upload_failed_php_error": "There was an error uploading the file.",
    "invalid_date": "Please enter a date in YYYY-MM-DD format.",
    "date_too_early": "This field has a too early date.",
    "date_too_late": "This field has a too late date.",
    "invalid_number": "Please enter a number.",
    "number_too_small": "This field has a too small number.",
    "number_too_large": "This field has a too large number.",
    "quiz_answer_not_correct": "The answer to the quiz is incorrect.",
    "invalid_email": "Please enter an email address.",
    "invalid_url": "Please enter a URL.",
    "invalid_tel": "Please enter a telephone number.",
    "captcha_not_match": "Your entered code is incorrect."
  },
  "additionalSettings": ""
}
```

## Storing Messages

Contact Form 7 only handles the form submissions themselves and emailing a designated recipient.

If you want to save message in your database as well, download the companion [Flamingo Plugin](https://wordpress.org/plugins/flamingo/).
