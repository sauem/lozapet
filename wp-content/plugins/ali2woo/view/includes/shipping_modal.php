<div class="modal-overlay modal-shipping">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('Available shipping methods', 'ali2woo'); ?></h3>
            <a class="modal-btn-close" href="#"><svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-cross"></use></svg></a>
        </div>
        <div class="modal-body">
            <div class="container-flex"><span><?php _e('Calculate your shipping price:', 'ali2woo'); ?></span>
                <div class="country-select" id="my-list">
                    <select id="modal-country-select" class="form-control country_list" style="width: 100%;">
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['c']; ?>"<?php if (isset($filter['country']) && $filter['country'] == $country['c']): ?> selected="selected"<?php endif; ?>><?php echo $country['n']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="message-container">
                <div class="shipping-method"> <span class="shipping-method-title"><?php _e('These are the shipping methods you will be able to select when processing orders:', 'ali2woo'); ?></span>
                    <div class="shipping-method">
                        <table class="shipping-table">
                            <thead>
                                <tr>
                                    <th><strong><?php _e('Shipping Method', 'ali2woo'); ?></strong></th>
                                    <th><strong><?php _e('Estimated Delivery Time', 'ali2woo'); ?></strong></th>
                                    <th><strong><?php _e('Shipping Cost', 'ali2woo'); ?></strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Free Worldwide Shipping</td>
                                    <td>19-39</td>
                                    <td>$0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default modal-close" type="button"><?php _e('Ok', 'ali2woo'); ?></button>
        </div>
    </div>
</div>

