<?php
/**
 * Plugin Name: DHL Express Commerce
 * Plugin URI: https://www.dhlexpresscommerce.com/
 * Description: Provides DHL Express shipping rates at checkout.
 * Author: DHL Express
 * Author URI: https://www.dhlexpresscommerce.com/
 * Version: 3.0.0
 * 
 * Copyright (c) 2019 StarShipIT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function dhlexpress_is_woocommerce_active() {
	
	$wc_active = (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))));
	
	if ($wc_active) {
		return $wc_active;
	} else {
        if (is_multisite()) {
			// WordPress multisite detected
			$wc_active = (array_key_exists('woocommerce/woocommerce.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins'))));
		}
    }
	
	return $wc_active;
}

// Ensure WooCommerce is active
if (dhlexpress_is_woocommerce_active()) {

  function dhlexpress_services_init() {
  
    if (! class_exists('WC_DHLExpress_Rates')) {
        
      class WC_DHLExpress_Rates extends WC_Shipping_Method {

        public function __construct() {
          $this -> id = 'dhlexpress';
          $this -> method_title = __('DHL Express Shipping Rates');
          $this -> method_description = __('Display live shipping rates at checkout via DHL Express API service');
          
          $this -> apikey = "";
          $this -> enabled = "yes";
          $this -> init();
        } 

        function init() {
          $this -> init_form_fields();
          $this -> init_settings();
          $this -> apikey = $this -> settings['apikey'];
          $this -> enabled = $this -> settings['enabled'];
          
          add_action('woocommerce_update_options_shipping_' . $this -> id, array($this, 'process_admin_options'));
        }
        
        function init_form_fields() {
          $this -> form_fields = array(
            'apikey' => array(
            'title' => __('API Key', 'woocommerce'),
            'type' => 'text',
            'description' => __('This is available under your DHL App account (Settings > API).', 'woocommerce')
            ),
            'enabled' => array(
              'type' => 'checkbox',
              'label' => __('Enable rates at checkout', 'woocommerce'),
              'default' => 'yes'
              )
            );
        }
          
        public function calculate_shipping($package = array()) {
          if ($this -> enabled == 'no') {
            return;
          }
          
          try {
            $address = $package['destination']['address'];
            $address_2 = $package['destination']['address_2'];
            $city = $package['destination']['city'];
            $state = $package['destination']['state'];
            $postcode = $package['destination']['postcode'];
            $countrycode = $package['destination']['country'];
            
            $counter = 0;
            $itemList = '';

            try {
              $dimension_unit = get_option('woocommerce_dimension_unit');

              foreach ($package['contents'] as $package_item) {
                $product = $package_item[ 'data' ];
                $productName = str_replace('"', '\\"', $product->get_title());;
                $productPrice = $product->get_price();
                $quantity = $package_item[ 'quantity' ];
              
                if (($quantity > 0) && $product -> needs_shipping()) {
                  $weight = $product -> get_weight();
                  $height = 0;
                  $length = 0;
                  $width = 0;
                
                  if ($product -> has_dimensions()) {
                    $height = $product -> get_height();
                    $length = $product -> get_length();
                    $width = $product -> get_width();
                  }

                  $itemList .= '{
                    "name": "' . $productName . '",
                    "sku": null,
                    "quantity": ' . $quantity . ',
                    "grams": ' . $weight . ',
                    "height": ' . $height . ',
                    "width": ' . $width . ',
                    "length": ' . $length . ',
                    "price": ' . $productPrice . ',
                    "dimensions_unit": "' . $dimension_unit . '",
                    "vendor": null,
                    "requires_shipping": true,
                    "taxable": true,
                    "fulfillment_service": "manual"
                  },';
                }

                if ($counter == count($package['contents']) - 1) {
                  $itemList = rtrim($itemList, ',');
                }

                $counter++;
              }
			}
            catch (Exception $e) {
              // backwards compatibility - get the rates using the old way
              $packageValue = $package['contents_cost'];
              $packageWeight = 0;

              foreach ($package['contents'] as $package_item) {
                $product = $package_item['data'];
                $quantity = $package_item['quantity'];
              
                if (($quantity > 0) && $product -> needs_shipping()) {
                  $weight = $product -> get_weight();
                  $packageWeight += $weight * $quantity;
                }
              }

              $itemList = '{
                "name": "Total Items",
                "sku": null,
                "quantity": 1,
                "grams": ' . $packageWeight . ' ,
                "price": ' . $packageValue . ',
                "vendor": null,
                "requires_shipping": true,
                "taxable": true,
                "fulfillment_service": "manual"
              }';

              // Add DHL Express Commerce Rates Exception Logging
              $wc_logger = new WC_Logger();
              $wc_logger->add('DHL-Express-Commerce', $e);
            }
            
            $url = 'https://api.starshipit.com/api/rates/shopify?apiKey=' . $this -> apikey . '&integration_type=woocommerce&version=3.0&format=json&source=DHL';
            $post_data = '{
                            "rate": {
                              "destination":{  
                                "country": "' . $countrycode . '",
                                "postal_code": "' . $postcode . '",
                                "province": "' . $state . '",
                                "city": "' . $city . '",
                                "name": null,
                                "address1": "' . $address . '",
                                "address2": "' . $address_2 . '",
                                "address3": null,
                                "phone": null,
                                "fax": null,
                                "address_type": null,
                                "company_name": null
                              },
                              "items":[' .
                                "$itemList" .
                              ']
                            }
                          }';
                          
            $response = wp_remote_post($url, array(
              'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
              'method' => 'POST',
              'body' => $post_data,
              'timeout' => 75,
              'sslverify' => 0
              )
            );
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            $json_obj = json_decode($response_body);
            $rates_obj = $json_obj -> {'rates'} ;
            
            if (is_countable($rates_obj) && count($rates_obj) > 0) {
              foreach($rates_obj as $rate) {
                if (is_object($rate)) {
                  $shipping_rate = array(
                  'id' => $this -> id . '_' . $rate -> {'service_code'},
                  'label' => $rate -> {'service_name'},
                  'cost' => $rate -> {'total_price'},
                  'calc_tax' => 'per_order'
                  );
                  
                  $this -> add_rate($shipping_rate);
                } 
              } 
            }
          } catch (Exception $e) {
            // Add DHL Express Commerce Rates Exception Logging
            $wc_logger = new WC_Logger();
            $wc_logger->add('DHL-Express-Commerce', $e);
          }
        } 
      } 
    } 
  }
  
  add_action('woocommerce_shipping_init', 'dhlexpress_services_init');
  
  function add_dhlexpress_rates($methods) {
    $methods['dhlexpress_rates'] = 'WC_DHLExpress_Rates';
    return $methods;
  }
  
  add_filter('woocommerce_shipping_methods', 'add_dhlexpress_rates');
} 