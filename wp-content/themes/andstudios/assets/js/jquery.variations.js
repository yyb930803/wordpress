/*global nasa_params_variations */
;(function ($, window, document, undefined) {
    /**
     * VariationForm_QickView class which handles variation forms and attributes.
     */
    var VariationForm_QickView = function ($form) {
        var self = this;

        self.$form = $form;
        self.$attributeFields = $form.find('.variations select');
        self.$singleVariation = $form.find('.single_variation');
        self.$singleVariationWrap = $form.find('.single_variation_wrap');
        self.$resetVariations = $form.find('.reset_variations');
        self.$product = $form.closest('.product');
        self.variationData = $form.data('product_variations');
        self.useAjax = false === self.variationData;
        self.xhr = false;
        self.loading = true;

        // Initial state.
        self.$singleVariationWrap.show();
        self.$form.off('.wc-variation-form');

        // Methods.
        self.getChosenAttributes = self.getChosenAttributes.bind(self);
        self.findMatchingVariations = self.findMatchingVariations.bind(self);
        self.isMatch = self.isMatch.bind(self);
        self.toggleResetLink = self.toggleResetLink.bind(self);

        // Events.
        $form.on('click.wc-variation-form', '.reset_variations', {variationForm: self}, self.onReset);
        $form.on('reload_product_variations', {variationForm: self}, self.onReload);
        $form.on('hide_variation', {variationForm: self}, self.onHide);
        $form.on('show_variation', {variationForm: self}, self.onShow);
        $form.on('click', '.single_add_to_cart_button', {variationForm: self}, self.onAddToCart);
        $form.on('reset_data', {variationForm: self}, self.onResetDisplayedVariation);
        $form.on('reset_image', {variationForm: self}, self.onResetImage);
        $form.on('change.wc-variation-form', '.variations select', {variationForm: self}, self.onChange);
        $form.on('found_variation.wc-variation-form', {variationForm: self}, self.onFoundVariation);
        $form.on('check_variations.wc-variation-form', {variationForm: self}, self.onFindVariation);
        $form.on('update_variation_values.wc-variation-form', {variationForm: self}, self.onUpdateAttributes);

        // Init after gallery.
        setTimeout(function () {
            $form.trigger('check_variations');
            $form.trigger('wc_variation_form');
            self.loading = false;
        }, 100);
    };

    /**
     * Reset all fields.
     */
    VariationForm_QickView.prototype.onReset = function (event) {
        event.preventDefault();
        event.data.variationForm.$attributeFields.val('').change();
        event.data.variationForm.$form.trigger('reset_data');
    };

    /**
     * Reload variation data from the DOM.
     */
    VariationForm_QickView.prototype.onReload = function (event) {
        var form = event.data.variationForm;
        form.variationData = form.$form.data('product_variations');
        form.useAjax = false === form.variationData;
        form.$form.trigger('check_variations');
    };

    /**
     * When a variation is hidden.
     */
    VariationForm_QickView.prototype.onHide = function (event) {
        event.preventDefault();
        event.data.variationForm.$form.find('.single_add_to_cart_button').removeClass('wc-variation-is-unavailable').addClass('disabled wc-variation-selection-needed');
        event.data.variationForm.$form.find('.woocommerce-variation-add-to-cart').removeClass('woocommerce-variation-add-to-cart-enabled').addClass('woocommerce-variation-add-to-cart-disabled');
    };

    /**
     * When a variation is shown.
     */
    VariationForm_QickView.prototype.onShow = function (event, variation, purchasable) {
        event.preventDefault();
        if (purchasable) {
            event.data.variationForm.$form.find('.single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed wc-variation-is-unavailable');
            event.data.variationForm.$form.find('.woocommerce-variation-add-to-cart').removeClass('woocommerce-variation-add-to-cart-disabled').addClass('woocommerce-variation-add-to-cart-enabled');
        } else {
            event.data.variationForm.$form.find('.single_add_to_cart_button').removeClass('wc-variation-selection-needed').addClass('disabled wc-variation-is-unavailable');
            event.data.variationForm.$form.find('.woocommerce-variation-add-to-cart').removeClass('woocommerce-variation-add-to-cart-enabled').addClass('woocommerce-variation-add-to-cart-disabled');
        }
    };

    /**
     * When the cart button is pressed.
     */
    VariationForm_QickView.prototype.onAddToCart = function (event) {
        if ($(this).is('.disabled')) {
            event.preventDefault();

            if ($(this).is('.wc-variation-is-unavailable')) {
                window.alert(nasa_params_variations.i18n_unavailable_text);
            } else if ($(this).is('.wc-variation-selection-needed')) {
                window.alert(nasa_params_variations.i18n_make_a_selection_text);
            }
        }
    };

    /**
     * When displayed variation data is reset.
     */
    VariationForm_QickView.prototype.onResetDisplayedVariation = function (event) {
        var form = event.data.variationForm;
        form.$product.find('.product_meta').find('.sku').wc_reset_content();
        form.$product.find('.product_weight').wc_reset_content();
        form.$product.find('.product_dimensions').wc_reset_content();
        form.$form.trigger('reset_image');
        form.$singleVariation.slideUp(200).trigger('hide_variation');
    };

    /**
     * When the product image is reset.
     */
    VariationForm_QickView.prototype.onResetImage = function (event) {
        event.data.variationForm.$form.lightbox_wc_variations_image_update(false);
    };

    /**
     * Looks for matching variations for current selected attributes.
     */
    VariationForm_QickView.prototype.onFindVariation = function (event) {
        var form = event.data.variationForm,
                attributes = form.getChosenAttributes(),
                currentAttributes = attributes.data;

        if (attributes.count === attributes.chosenCount) {
            if (form.useAjax) {
                if (form.xhr) {
                    form.xhr.abort();
                }
                form.$form.block({message: null, overlayCSS: {background: '#fff', opacity: 0.6}});
                currentAttributes.product_id = parseInt(form.$form.data('product_id'), 10);
                currentAttributes.custom_data = form.$form.data('custom_data');
                form.xhr = $.ajax({
                    url: nasa_params_variations.wc_ajax_url.toString().replace('%%endpoint%%', 'get_variation'),
                    type: 'POST',
                    data: currentAttributes,
                    success: function (variation) {
                        if (variation) {
                            form.$form.trigger('found_variation', [variation]);
                        } else {
                            form.$form.trigger('reset_data');
                            attributes.chosenCount = 0;

                            if (!form.loading) {
                                form.$form.find('.single_variation').after('<p class="wc-no-matching-variations woocommerce-info">' + nasa_params_variations.i18n_no_matching_variations_text + '</p>');
                                form.$form.find('.wc-no-matching-variations').slideDown(200);
                            }
                        }
                    },
                    complete: function () {
                        form.$form.unblock();
                    }
                });
            } else {
                form.$form.trigger('update_variation_values');

                var matching_variations = form.findMatchingVariations(form.variationData, currentAttributes),
                    variation = matching_variations.shift();

                if (variation) {
                    form.$form.trigger('found_variation', [variation]);
                } else {
                    form.$form.trigger('reset_data');
                    attributes.chosenCount = 0;

                    if (!form.loading) {
                        form.$form.find('.single_variation').after('<p class="wc-no-matching-variations woocommerce-info">' + nasa_params_variations.i18n_no_matching_variations_text + '</p>');
                        form.$form.find('.wc-no-matching-variations').slideDown(200);
                    }
                }
            }
        } else {
            form.$form.trigger('update_variation_values');
            form.$form.trigger('reset_data');
        }

        // Show reset link.
        form.toggleResetLink(attributes.chosenCount > 0);
    };

    /**
     * Triggered when a variation has been found which matches all attributes.
     */
    VariationForm_QickView.prototype.onFoundVariation = function (event, variation) {
        var form = event.data.variationForm,
            $sku = form.$product.find('.product_meta').find('.sku'),
            $weight = form.$product.find('.product_weight'),
            $dimensions = form.$product.find('.product_dimensions'),
            $qty = form.$singleVariationWrap.find('.quantity'),
            purchasable = true,
            variation_id = '',
            template = false,
            $template_html = '';

        if (variation.sku) {
            $sku.wc_set_content(variation.sku);
        } else {
            $sku.wc_reset_content();
        }

        if (variation.weight) {
            $weight.wc_set_content(variation.weight_html);
        } else {
            $weight.wc_reset_content();
        }

        if (variation.dimensions) {
            // Decode HTML entities.
            $dimensions.wc_set_content($.parseHTML(variation.dimensions_html)[0].data);
        } else {
            $dimensions.wc_reset_content();
        }

        form.$form.lightbox_wc_variations_image_update(variation);

        if (!variation.variation_is_visible) {
            template = nasa_template('unavailable-variation-template');
        } else {
            template = nasa_template('variation-template');
            variation_id = variation.variation_id;
        }

        $template_html = nasa_replace_template({
            variation: variation
        }, template);
        $template_html = $template_html.replace('/*<![CDATA[*/', '');
        $template_html = $template_html.replace('/*]]>*/', '');

        form.$singleVariation.html($template_html);
        form.$form.find('input[name="variation_id"], input.variation_id').val(variation.variation_id).change();

        // Hide or show qty input
        if (variation.is_sold_individually === 'yes') {
            $qty.find('input.qty').val('1').attr('min', '1').attr('max', '');
            $qty.hide();
        } else {
            $qty.find('input.qty').attr('min', variation.min_qty).attr('max', variation.max_qty);
            $qty.show();
        }

        // Enable or disable the add to cart button
        if (!variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible) {
            purchasable = false;
        }

        // Reveal
        if ($.trim(form.$singleVariation.text())) {
            form.$singleVariation.slideDown(200).trigger('show_variation', [variation, purchasable]);
        } else {
            form.$singleVariation.show().trigger('show_variation', [variation, purchasable]);
        }
    };

    /**
     * Triggered when an attribute field changes.
     */
    VariationForm_QickView.prototype.onChange = function (event) {
        var form = event.data.variationForm;

        form.$form.find('input[name="variation_id"], input.variation_id').val('').change();
        form.$form.find('.wc-no-matching-variations').remove();

        if (form.useAjax) {
            form.$form.trigger('check_variations');
        } else {
            form.$form.trigger('woocommerce_variation_select_change');
            form.$form.trigger('check_variations');
            $(this).blur();
        }

        // Custom event for when variation selection has been changed
        form.$form.trigger('woocommerce_variation_has_changed');
    };

    /**
     * Escape quotes in a string.
     * @param {string} string
     * @return {string}
     */
    VariationForm_QickView.prototype.addSlashes = function (string) {
        string = string.replace(/'/g, '\\\'');
        string = string.replace(/"/g, '\\\"');
        return string;
    };

    /**
     * Updates attributes in the DOM to show valid values.
     */
    VariationForm_QickView.prototype.onUpdateAttributes = function (event) {
        var form = event.data.variationForm,
            attributes = form.getChosenAttributes(),
            currentAttributes = attributes.data;

        if (form.useAjax) {
            return;
        }

        // Loop through selects and disable/enable options based on selections.
        form.$attributeFields.each(function (index, el) {
            var current_attr_select = $(el),
                current_attr_name = current_attr_select.data('attribute_name') || current_attr_select.attr('name'),
                show_option_none = $(el).data('show_option_none'),
                option_gt_filter = ':gt(0)',
                attached_options_count = 0,
                new_attr_select = $('<select/>'),
                selected_attr_val = current_attr_select.val() || '',
                selected_attr_val_valid = true;

            // Reference options set at first.
            if (!current_attr_select.data('attribute_html')) {
                var refSelect = current_attr_select.clone();

                refSelect.find('option').removeAttr('disabled attached').removeAttr('selected');

                current_attr_select.data('attribute_options', refSelect.find('option' + option_gt_filter).get()); // Legacy data attribute.
                current_attr_select.data('attribute_html', refSelect.html());
            }

            new_attr_select.html(current_attr_select.data('attribute_html'));

            // The attribute of this select field should not be taken into account when calculating its matching variations:
            // The constraints of this attribute are shaped by the values of the other attributes.
            var checkAttributes = $.extend(true, {}, currentAttributes);
            // console.log(currentAttributes);

            checkAttributes[ current_attr_name ] = '';
            // console.log(checkAttributes);

            var variations = form.findMatchingVariations(form.variationData, checkAttributes);
            // console.log(variations);

            // Loop through variations.
            for (var num in variations) {
                if (typeof (variations[ num ]) !== 'undefined') {
                    var variationAttributes = variations[ num ].attributes;

                    for (var attr_name in variationAttributes) {
                        if (variationAttributes.hasOwnProperty(attr_name)) {
                            var attr_val = variationAttributes[ attr_name ],
                                variation_active = '';

                            if (attr_name === current_attr_name) {
                                if (variations[ num ].variation_is_active) {
                                    variation_active = 'enabled';
                                }

                                if (attr_val) {
                                    // Decode entities and add slashes.
                                    attr_val = $('<div/>').html(attr_val).text();

                                    // Attach.
                                    new_attr_select.find('option[value="' + form.addSlashes(attr_val) + '"]').addClass('attached ' + variation_active);
                                } else {
                                    // Attach all apart from placeholder.
                                    new_attr_select.find('option:gt(0)').addClass('attached ' + variation_active);
                                }
                            }
                        }
                    }
                }
            }

            // Count available options.
            attached_options_count = new_attr_select.find('option.attached').length;

            // Check if current selection is in attached options.
            if (selected_attr_val && (attached_options_count === 0 || new_attr_select.find('option.attached.enabled[value="' + form.addSlashes(selected_attr_val) + '"]').length === 0)) {
                selected_attr_val_valid = false;
            }

            // Detach the placeholder if:
            // - Valid options exist.
            // - The current selection is non-empty.
            // - The current selection is valid.
            // - Placeholders are not set to be permanently visible.
            if (attached_options_count > 0 && selected_attr_val && selected_attr_val_valid && ('no' === show_option_none)) {
                new_attr_select.find('option:first').remove();
                option_gt_filter = '';
            }

            // Detach unattached.
            new_attr_select.find('option' + option_gt_filter + ':not(.attached)').remove();

            // Finally, copy to DOM and set value.
            current_attr_select.html(new_attr_select.html());
            current_attr_select.find('option' + option_gt_filter + ':not(.enabled)').prop('disabled', true);

            // Choose selected value.
            if (selected_attr_val) {
                // If the previously selected value is no longer available, fall back to the placeholder (it's going to be there).
                if (selected_attr_val_valid) {
                    current_attr_select.val(selected_attr_val);
                } else {
                    current_attr_select.val('').change();
                }
            } else {
                current_attr_select.val(''); // No change event to prevent infinite loop.
            }
        });
        
        /**
         * Support Gallery images
         */
        if($('.product-lightbox').find('.nasa-gallery-variation-supported').length) {
            if(!_quicked_gallery && typeof _lightbox_variations[0] !== 'undefined') {
                _quicked_gallery = true;
                var result = _lightbox_variations[0];
                /**
                 * Main image
                 */
                if(typeof result.quickview_gallery !== 'undefined') {
                    $('.nasa-product-gallery-lightbox').find('.main-image-slider').replaceWith(result.quickview_gallery);
                }

                loadLightboxCarousel($);
            }
        }

        /**
         * deal time
         */
        if ($('.nasa-quickview-product-deal-countdown').length) {
            $('.nasa-quickview-product-deal-countdown').html('');
            $('.nasa-quickview-product-deal-countdown').removeClass('nasa-show');
        }

        // Custom event for when variations have been updated.
        form.$form.trigger('woocommerce_update_variation_values');
    };

    /**
     * Get chosen attributes from form.
     * @return array
     */
    VariationForm_QickView.prototype.getChosenAttributes = function () {
        var data = {};
        var count = 0;
        var chosen = 0;

        this.$attributeFields.each(function () {
            var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
            var value = $(this).val() || '';

            if (value.length > 0) {
                chosen++;
            }

            count++;
            data[ attribute_name ] = value;
        });

        return {
            'count': count,
            'chosenCount': chosen,
            'data': data
        };
    };

    /**
     * Find matching variations for attributes.
     */
    VariationForm_QickView.prototype.findMatchingVariations = function (variations, attributes) {
        var matching = [];
        for (var i = 0; i < variations.length; i++) {
            var variation = variations[i];

            if (this.isMatch(variation.attributes, attributes)) {
                matching.push(variation);
            }
        }
        
        return matching;
    };

    /**
     * See if attributes match.
     * @return {Boolean}
     */
    VariationForm_QickView.prototype.isMatch = function (variation_attributes, attributes) {
        var match = true;
        for (var attr_name in variation_attributes) {
            if (variation_attributes.hasOwnProperty(attr_name)) {
                var val1 = variation_attributes[ attr_name ];
                var val2 = attributes[ attr_name ];
                if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
                    match = false;
                }
            }
        }
        return match;
    };

    /**
     * Show or hide the reset link.
     */
    VariationForm_QickView.prototype.toggleResetLink = function (on) {
        if (on) {
            if (this.$resetVariations.css('visibility') === 'hidden') {
                this.$resetVariations.css('visibility', 'visible').hide().fadeIn();
            }
        } else {
            this.$resetVariations.css('visibility', 'hidden');
        }
    };

    /**
     * Function to call wc_variation_form on jquery selector.
     */
    $.fn.wc_variation_form_lightbox = function () {
        new VariationForm_QickView(this);
        return this;
    };

    /**
     * Stores the default text for an element so it can be reset later
     */
    $.fn.wc_set_content = function (content) {
        if (undefined === this.attr('data-o_content')) {
            this.attr('data-o_content', this.text());
        }
        this.text(content);
    };

    /**
     * Stores the default text for an element so it can be reset later
     */
    $.fn.wc_reset_content = function () {
        if (undefined !== this.attr('data-o_content')) {
            this.text(this.attr('data-o_content'));
        }
    };

    /**
     * Stores a default attribute for an element so it can be reset later
     */
    $.fn.wc_set_variation_attr = function (attr, value) {
        if (undefined === this.attr('data-o_' + attr)) {
            this.attr('data-o_' + attr, (!this.attr(attr)) ? '' : this.attr(attr));
        }
        if (false === value) {
            this.removeAttr(attr);
        } else {
            this.attr(attr, value);
        }
    };

    /**
     * Reset a default attribute for an element so it can be reset later
     */
    $.fn.wc_reset_variation_attr = function (attr) {
        if (undefined !== this.attr('data-o_' + attr)) {
            this.attr(attr, this.attr('data-o_' + attr));
        }
    };

    /**
     * Reset the slide position if the variation has a different image than the current one
     */
    $.fn.wc_maybe_trigger_slide_position_reset = function (variation) {
        var $form = $(this),
            $product = $form.closest('.product'),
            $product_gallery = $product.find('.images'),
            reset_slide_position = false,
            new_image_id = (variation && variation.image_id) ? variation.image_id : '';

        if ($form.attr('current-image') !== new_image_id) {
            reset_slide_position = true;
        }

        $form.attr('current-image', new_image_id);

        if (reset_slide_position) {
            $product_gallery.trigger('woocommerce_gallery_reset_slide_position');
        }
    };

    /**
     * Sets product images for the chosen variation
     */
    $.fn.lightbox_wc_variations_image_update = function (variation) {
        var $form = this;
        
        if (variation && variation.image && variation.image.src && variation.image.src.length > 1) {
            /**
             * Support Gallery images
             */
            if($('.product-lightbox').find('.nasa-gallery-variation-supported').length) {
                var _data = {
                    'variation_id': variation.variation_id,
                    'is_purchasable': variation.is_purchasable,
                    'is_in_stock': variation.is_in_stock,
                    'main_id': typeof variation.image_id !== 'undefined' ? variation.image_id : 0,
                    'gallery': typeof variation.nasa_gallery_variation !== 'undefined' ?
                        variation.nasa_gallery_variation : [],
                    'show_images': $('.product-lightbox').find('.main-image-slider').attr('data-items')
                };
                
                changeGalleryVariableQuickviewProduct($, _data);
            } else {
                var _src_large = typeof variation.image_single_page !== 'undefined' ?
                    variation.image_single_page : variation.image.url;

                $('.main-image-slider .owl-item:eq(0) img').attr('src', _src_large);
                $('.main-image-slider .owl-item:eq(0) img').removeAttr('srcset');
            }
        } else {
            $form.wc_variations_image_reset();
        }

        /**
         * deal time
         */
        if ($('.product-lightbox').find('.nasa-gallery-variation-supported').length < 1 && $('.nasa-quickview-product-deal-countdown').length) {
            if (
                variation && variation.variation_id &&
                variation.is_in_stock && variation.is_purchasable
            ) {
                if(typeof _single_variations[variation.variation_id] === 'undefined') {
                    var _urlAjax = null;
                    if(
                        typeof wc_add_to_cart_params !== 'undefined' &&
                        typeof wc_add_to_cart_params.wc_ajax_url !== 'undefined'
                    ) {
                        _urlAjax = wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'nasa_get_deal_variation');
                    }

                    if(_urlAjax) {
                        $.ajax({
                            // url: ajaxurl,
                            url: _urlAjax,
                            type: 'post',
                            cache: false,
                            data: {
                                pid: variation.variation_id
                            },
                            beforeSend: function () {
                                $('.nasa-quickview-product-deal-countdown').html('');
                                $('.nasa-quickview-product-deal-countdown').removeClass('nasa-show');
                            },
                            success: function (res) {
                                if(typeof res.success !== 'undefined' && res.success === '1') {
                                    _single_variations[variation.variation_id] = res.content;
                                } else {
                                    _single_variations[variation.variation_id] = '';
                                }
                                $('.nasa-quickview-product-deal-countdown').html(_single_variations[variation.variation_id]);
                                if(_single_variations[variation.variation_id] !== '') {
                                    loadCountDown($);
                                    if(!$('.nasa-quickview-product-deal-countdown').hasClass('nasa-show')) {
                                        $('.nasa-quickview-product-deal-countdown').addClass('nasa-show');
                                    }
                                } else {
                                    $('.nasa-quickview-product-deal-countdown').removeClass('nasa-show');
                                }
                            }
                        });
                    }
                } else {
                    $('.nasa-quickview-product-deal-countdown').html(_single_variations[variation.variation_id]);
                    if(_single_variations[variation.variation_id] !== '') {
                        loadCountDown($);
                        if(!$('.nasa-quickview-product-deal-countdown').hasClass('nasa-show')) {
                            $('.nasa-quickview-product-deal-countdown').addClass('nasa-show');
                        }
                    } else {
                        $('.nasa-quickview-product-deal-countdown').removeClass('nasa-show');
                    }
                }
            } else {
                $('.nasa-quickview-product-deal-countdown').html('');
                $('.nasa-quickview-product-deal-countdown').removeClass('nasa-show');
            }
        }
    };

    /**
     * Reset main image to defaults.
     */
    $.fn.wc_variations_image_reset = function () {
        if($('.product-lightbox').find('.nasa-gallery-variation-supported').length) {
            if(!_quicked_gallery && typeof _lightbox_variations[0] !== 'undefined') {
                _quicked_gallery = true;
                var result = _lightbox_variations[0];
                /**
                 * Main image
                 */
                if(typeof result.quickview_gallery !== 'undefined') {
                    $('.nasa-product-gallery-lightbox').find('.main-image-slider').replaceWith(result.quickview_gallery);
                }

                loadLightboxCarousel($);
            }
        }
        
        else {
            var image_large = $('.nasa-product-gallery-lightbox').attr('data-o_href');
            $('.main-image-slider .owl-item:eq(0) img').attr('src', image_large).removeAttr('srcset');
        }
    };

    /**
     * Matches inline variation objects to chosen attributes
     * @deprecated 2.6.9
     * @type {Object}
     */
    var wc_variation_form_matcher = {
        find_matching_variations: function (product_variations, settings) {
            var matching = [];
            for (var i = 0; i < product_variations.length; i++) {
                var variation = product_variations[i];

                if (wc_variation_form_matcher.variations_match(variation.attributes, settings)) {
                    matching.push(variation);
                }
            }
            return matching;
        },
        variations_match: function (attrs1, attrs2) {
            var match = true;
            for (var attr_name in attrs1) {
                if (attrs1.hasOwnProperty(attr_name)) {
                    var val1 = attrs1[ attr_name ];
                    var val2 = attrs2[ attr_name ];
                    if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
                        match = false;
                    }
                }
            }
            return match;
        }
    };
    
    /**
     * 
     * @param {type} templateId
     * @returns {String}
     */
    var nasa_template = function (templateId) {
        return document.getElementById('tmpl-' + templateId + '-nasa').textContent;
    };

})(jQuery, window, document);

function nasa_replace_template(data, html) {
    var variation = data.variation || {};

    if(html !== '') {
        if(typeof variation.variation_description !== 'undefined') {
            html = html.replace('{{{data.variation.variation_description}}}', variation.variation_description);
        }

        if(typeof variation.variation_description !== 'undefined') {
            html = html.replace('{{{data.variation.price_html}}}', variation.price_html);
        }

        if(typeof variation.variation_description !== 'undefined') {
            html = html.replace('{{{data.variation.availability_html}}}', variation.availability_html);
        }
    }
    
    return html;
}
