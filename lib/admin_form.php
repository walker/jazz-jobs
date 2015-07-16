<div id="stripe-payments-admin-wrap" class="wrap">
    <h2>Jazz - Options</h2>
    <form name="resumator_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="resumator_api_key">API Key:</label></th>
                    <td><input type="text" name="resumator_api_key" id="resumator_api_key" value="<?php echo $resumator_api_key; ?>" /></td>
                </tr>
                <?php // if(isset($r_pages) && !empty($r_pages)) { ?>
                <tr>
                    <th scope="row"><label for="resumator_url_path">Jazz URL Path:</label></th>
                    <td>
                        <input type="text" name="resumator_url_path" id="resumator_url_path" placeholder="/" <?php if(!empty($resumator_url_path)) { echo 'value="'.$resumator_url_path.'" '; } ?>/>
                    </td>
                </tr>
                <?php // } ?>
                <?php if(empty($jobs_dd)) { ?>
                  <tr style="display:none;">
                    <td>
                      <input type="hidden" name="resumator_job_bank" value="" />
                    </td>
                  </tr>
                <?php } else { ?>
                  <tr>
                    <th scope="row"><label for="resumator_job_bank">Job Bank Listing:</label></th>
                    <td>
                      <select name="resumator_job_bank" id="resumator_job_bank">
                        <option value="">Select a job bank</option>
                        <?php foreach($jobs_dd as $k => $v) { ?>
                          <option value="<?php echo $k; ?>"<?php if($resumator_job_bank==$k) { echo ' selected="selected"'; } ?>><?php echo $v; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" class="button button-primary" value="Save Changes" value="<?php _e('Save Changes'); ?>" />
        </p>
    </form>
</div>
