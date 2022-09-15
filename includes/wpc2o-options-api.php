<?php

/**
 * TODO
 * @return string 
 */
function wpc2o_get_api_view(): string
{
    $content  = '<h1>API Credentials</h1>';
    $content .= '<div style="padding: 0 12px">';
    $content .= '<p>Key: ' . get_option(constant('WPC2O_API_KEY')) . '';
    $content .= '<p>Order endpoint: ' . get_option(constant('WPC2O_API_ENDPOINT')) . '';
    $content .= '<p>Stock endpoint: ' . get_option(constant('WPC2O_API_STOCK_ENDPOINT')) . '';
    $content .= '<p>Store manager email: ' . get_option(constant('WPC2O_API_STORE_MANAGER_EMAIL')) . '';
    $content .= '<p style="padding: 10px 0 0 0;"><a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wpc2o">Update your API credentials</a>.</p>';
    $content .= '</div>';
    return $content;
}

/**
 * TODO
 * @return string 
 */
function wpc2o_get_example_post_request_view(): string
{
    $current_user      = wp_get_current_user();
    $current_user_meta = get_user_meta($current_user->ID);
    $data              = '<pre id="wpc2o-example-json"><code>';
    $json              = '
        {
                "api_key": "' . get_option(constant('WPC2O_API_KEY')) . '",
                "order": {
                    "order_id": "_",
                    "order_notes": "_",
                    "delivery_method": ""
                },
                "customer": {
                    "name": "' . $current_user->display_name . '",
                    "email": "' . $current_user->user_email . '",
                    "telephone": "' . $current_user_meta['shipping_phone'][0] . '"
                },
                "address": {
                    "delivery_name": "' . $current_user_meta['shipping_first_name'][0] . ' ' . $current_user_meta['shipping_last_name'][0] . '",
                    "company_name": "' . $current_user_meta['shipping_company'][0] . '",
                    "address_line_1": "' . $current_user_meta['shipping_address_1'][0] . '",
                    "address_line_2": "' . $current_user_meta['shipping_address_2'][0] . '",
                    "city": "' . $current_user_meta['shipping_city'][0] . '",
                    "postcode": "' . $current_user_meta['shipping_postcode'][0] . '",
                    "country": "' . $current_user_meta['shipping_country'][0] . '"
                },
                "products": {
                    "product": [
                        {
                            "sku": "_",
                            "quantity": "_",
                            "logos": {
                                "logo": [
                                    {
                                        "unique_id": "_3_8",
                                        "file": "",
                                        "position": "3",
                                        "width": "8",
                                        "type": "print"
                                    },
                                    {
                                        "unique_id": "_5_12",
                                        "file": "",
                                        "position": "5",
                                        "width": "12",
                                        "type": "print"
                                    }
                                ]
                            }
                        }
                    ]
                }
            }
        ';
    $data             .= $json;
    $data             .= '</code></pre>';
    return $data;
}