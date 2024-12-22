export const contactForm7TemplateVue = `<script>
import { useCf7Form } from "@evo-mark/inertia-wordpress/vue";

const { form, formRef, fields, cf7Form } = useCf7Form("contact-form-1");
</script>

<template>
    <form ref="formRef" @submit.prevent="form.submit">
        <template v-for="field in fields">
            <input v-model="form[field]" :placeholder="field" /><br />
            <span>{{ form.errors[field] }}</span><br />
        </template>
        <button type="submit">Submit</button>
        <span>{{ form.errors.recaptcha }}</span>
    </form>
</template>`;

export const contactForm7TemplateReact = `import { useCf7Form } from "@evo-mark/inertia-wordpress/react";

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
                        onChange={($event) =>
                            setData(field, $event.target.value)
                        }
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

export default Component;`;

export const contactForm7TemplateSvelte = `
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
`;
