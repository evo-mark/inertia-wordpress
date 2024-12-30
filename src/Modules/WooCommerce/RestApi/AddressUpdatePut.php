<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\RestApi;

use EvoWpRestRegistration\BaseRestController;
use WP_REST_Request;

class AddressUpdatePut extends BaseRestController
{
    protected $path = 'modules/woocommerce/address';
    protected $methods = 'PUT';

    protected $rules = [
        'username' => ['required', 'string'],
        'postcode' => ['required', 'string'],
        'type' => ['required', 'string', 'in:billing,shipping'],
        '_nonce' => ['nullable', 'string'],
    ];

    public function handler(WP_REST_Request $request)
    {
        global $wp;
        $validated = $this->validated();

        $validNonce = wp_verify_nonce($validated['_nonce'], 'woocommerce-edit_address');
        $userId = get_current_user_id();

        if ($userId <= 0) {
            return;
        }

        $customer = new \WC_Customer($userId);

        if (! $customer) {
            return;
        }

        $addressType = isset($validated['type']) ? wc_edit_address_i18n(sanitize_title($validated['type']), true) : 'billing';

        if (! isset($_POST[$address_type . '_country'])) {
            return;
        }

        $address = WC()->countries->get_address_fields(wc_clean(wp_unslash($_POST[$address_type . '_country'])), $address_type . '_');

        foreach ($address as $key => $field) {
            if (! isset($field['type'])) {
                $field['type'] = 'text';
            }

            // Get Value.
            if ('checkbox' === $field['type']) {
                $value = (int) isset($_POST[$key]);
            } else {
                $value = isset($_POST[$key]) ? wc_clean(wp_unslash($_POST[$key])) : '';
            }

            // Hook to allow modification of value.
            $value = apply_filters('woocommerce_process_myaccount_field_' . $key, $value);


            if (! empty($value)) {
                // Validation and formatting rules.
                if (! empty($field['validate']) && is_array($field['validate'])) {
                    foreach ($field['validate'] as $rule) {
                        switch ($rule) {
                            case 'postcode':
                                $country = wc_clean(wp_unslash($_POST[$address_type . '_country']));
                                $value   = wc_format_postcode($value, $country);

                                if ('' !== $value && ! WC_Validation::is_postcode($value, $country)) {
                                    switch ($country) {
                                        case 'IE':
                                            $postcode_validation_notice = __('Please enter a valid Eircode.', 'woocommerce');
                                            break;
                                        default:
                                            $postcode_validation_notice = __('Please enter a valid postcode / ZIP.', 'woocommerce');
                                    }
                                    wc_add_notice($postcode_validation_notice, 'error');
                                }
                                break;
                            case 'phone':
                                if ('' !== $value && ! WC_Validation::is_phone($value)) {
                                    /* translators: %s: Phone number. */
                                    wc_add_notice(sprintf(__('%s is not a valid phone number.', 'woocommerce'), '<strong>' . $field['label'] . '</strong>'), 'error');
                                }
                                break;
                            case 'email':
                                $value = strtolower($value);

                                if (! is_email($value)) {
                                    /* translators: %s: Email address. */
                                    wc_add_notice(sprintf(__('%s is not a valid email address.', 'woocommerce'), '<strong>' . $field['label'] . '</strong>'), 'error');
                                }
                                break;
                        }
                    }
                }
            }

            try {
                // Set prop in customer object.
                if (is_callable([$customer, "set_$key"])) {
                    $customer->{"set_$key"}($value);
                } else {
                    $customer->update_meta_data($key, $value);
                }
            } catch (WC_Data_Exception $e) {
                // Set notices. Ignore invalid billing email, since is already validated.
                if ('customer_invalid_billing_email' !== $e->getErrorCode()) {
                    wc_add_notice($e->getMessage(), 'error');
                }
            }
        }

        /**
         * Hook: woocommerce_after_save_address_validation.
         *
         * Allow developers to add custom validation logic and throw an error to prevent save.
         *
         * @since 3.6.0
         * @param int         $user_id User ID being saved.
         * @param string      $address_type Type of address; 'billing' or 'shipping'.
         * @param array       $address The address fields.
         * @param WC_Customer $customer The customer object being saved.
         */
        do_action('woocommerce_after_save_address_validation', $user_id, $address_type, $address, $customer);

        if (0 < wc_notice_count('error')) {
            return;
        }

        $customer->save();

        wc_add_notice(__('Address changed successfully.', 'woocommerce'));

        /**
         * Hook: woocommerce_customer_save_address.
         *
         * Fires after a customer address has been saved.
         *
         * @since 3.6.0
         * @param int    $user_id User ID being saved.
         * @param string $address_type Type of address; 'billing' or 'shipping'.
         */
        do_action('woocommerce_customer_save_address', $user_id, $address_type);

        wp_safe_redirect(wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount')));
        exit;
    }
}
