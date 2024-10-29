
<div id="aklamator-options" style="width:1160px;margin-top:10px;">

    <div style="float: left; width: 300px;">

        <a target="_blank" href="<?php echo $this->aklamator_url; ?>?utm_source=wp-plugin">
            <img style="border-radius:5px;border:0px;" src=" <?php echo CHROME_PUSH_AKLA_PLUGIN_URL.'images/logo.jpg'; ?>" /></a>
        <?php
        if ($this->application_id != '') : ?>
            <a target="_blank" href="<?php echo $this->aklamator_url; ?>dashboard?utm_source=wp-plugin">
                <img style="border:0px;margin-top:5px;border-radius:5px;" src="<?php echo CHROME_PUSH_AKLA_PLUGIN_URL.'images/dashboard.jpg'; ?>" /></a>

        <?php endif; ?>

        <a target="_blank" href="<?php echo $this->aklamator_url;?>contact?utm_source=wp-plugin-contact">
            <img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo CHROME_PUSH_AKLA_PLUGIN_URL.'images/support.jpg'; ?>" /></a>

        <a target="_blank" href="http://qr.rs/q/4649f"><img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="<?php echo CHROME_PUSH_AKLA_PLUGIN_URL.'images/promo-300x200.png'; ?>" /></a>

    </div>

    <div class="box">

        <h1>Automate Chrome Push</h1>

        <?php

        if (isset($this->api_data->error) || $this->application_id == '') : ?>
            <h3 style="margin-bottom: 10px">Step 1: Get your Aklamator Aplication ID</h3>
            <a class='aklamator_button aklamator-login-button' id="aklamator_login_button" >Click here for FREE registration/login</a>
            <div style="clear: both"></div>
            <p style="margin-top: 10px">Or you can manually <a href="<?php echo $this->aklamator_url . 'registration/publisher'; ?>" target="_blank">register</a> or <a href="<?php echo $this->aklamator_url . 'login'; ?>" target="_blank">login</a> and copy paste your Application ID</p>
            <script>var signup_url = '<?php echo $this->getSignupUrl(); ?>';</script>
        <?php endif; ?>



        <div style="clear: both"></div>
        <?php if ($this->application_id == '') { ?>
            <h3>Step 2: &nbsp;&nbsp;&nbsp;&nbsp; Paste your Aklamator Application ID</h3>
        <?php }else{ ?>
            <h3>Your Aklamator Application ID</h3>
        <?php } ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('aklamatorChrome-options');
            ?>

            <p >
                <input type="text" style="width: 400px" name="aklamatorChromeApplicationID" id="aklamatorChromeApplicationID" value="<?php echo $this->application_id; ?>" maxlength="48" />

            </p>
            <p>
                <input type="checkbox" id="aklamatorChromePoweredBy" name="aklamatorChromePoweredBy" <?php echo (get_option("aklamatorChromePoweredBy") == true ? 'checked="checked"' : ''); ?> Required="Required">
                <strong>Required</strong> I acknowledge there is a 'powered by aklamator' link on the widget. <br />
            </p>
            <p>
                <input type="checkbox" id="aklamatorChromeFeatured2Feed" name="aklamatorChromeFeatured2Feed" <?php echo (get_option("aklamatorChromeFeatured2Feed") == true ? 'checked="checked"' : ''); ?> >
                <strong>Add featured</strong> images from posts to your site's RSS feed output
            </p>

            <p>
            <div class="alert alert-msg">
                <strong>Note </strong><span style="color: red">*</span>: By default, posts without images will not be shown in widgets. If you want to show them click on <strong>EDIT</strong> in table below!
            </div>
            </p>
            <?php if(isset($this->api_data->flag) && $this->api_data->flag === false): ?>
                <p id="aklamator_infeed_inactive" class="alert_red alert-msg_red"><span style="color:red"><?php echo $this->api_data->error; ?></span></p>
            <?php endif; ?>

            <table class="form-table">

                <tr valign="top" <?php echo ($this->application_id == '')? 'style="display:none"' : ''; ?> >
                    <th scope="row" style="width: 200px">Send notifications for each new post types</th>
                    <td style="text-align: left; vertical-align: middle">
                        <?php
                        $post_types = get_post_types( array( 'public' => true ), 'objects' );

                        unset($post_types['attachment']);

                        $selected_post_types = get_option('aklamatorChromePostTypes');

                        foreach ( $post_types as $key => $post_type ): ?>

                            <input type="checkbox" value="<?php echo $key ?>" name="aklamatorChromePostTypes[]" <?php echo is_array($selected_post_types) && in_array($key, $selected_post_types) ? 'checked' : ''; ?>> <b> <?php echo $post_type->labels->name ?> </b><br/>

                        <?php endforeach; ?>

                    </td>
                </tr>

            <?php if($this->application_id !=='' && $this->api_data->flag): ?>


                    <tr valign="top">
                        <th scope="row" style="width: 200px">Your push notification image</th>
                        <td style="text-align: left; vertical-align: middle">
                            <img style="margin-bottom: 5px" src="<?php echo $this->aklamator_url."images/push_notifications/".$this->api_data->data->notification_img; ?>" width="120" height="120" alt="Account image" /> </br>
                            <a class="btn btn-primary" href = "<?php echo $this->aklamator_url;?>push/overview/edit/<?php echo $this->api_data->data->id;?>" target='_blank' title='Edit account settings'>Edit image </a>
                        </td>

                    </tr>



            <?php endif; ?>

            </table>
            <input id="aklamator_chrome_push_save" class="aklamator_INlogin" style ="margin: 0; border: 0; float: left;" type="submit" value="<?php echo (_e("Save Changes")); ?>" />
            <?php if(!isset($this->api_data->flag) || !$this->api_data->flag): ?>
                <div style="float: left; padding: 7px 0 0 10px; color: red; font-weight: bold; font-size: 16px"> <-- In order to proceed save changes</div>
            <?php endif ?>


        </form>
    </div>
    <!-- right sidebar -->
    <div class="right_sidebar">
        <iframe width="300" height="550" src="<?php echo $this->aklamator_url; ?>wp-sidebar/right?plugin=chrome-push" frameborder="0"></iframe>
    </div>
    <!-- End Right sidebar -->
</div>

<div style="clear:both"></div>
<div style="margin-top: 20px; margin-left: 0px; width: 810px;" class="box">

    <?php if ($this->curlfailovao && $this->application_id != ''): ?>
        <h2 style="color:red">Error communicating with Aklamator server, please refresh plugin page or try again later. </h2>
    <?php endif;?>
    <?php if(!isset($this->api_data->flag) || !$this->api_data->flag): ?>
        <a href="<?php echo $this->getSignupUrl(); ?>" target="_blank"><img style="border-radius:5px;border:0px;" src=" <?php echo CHROME_PUSH_AKLA_PLUGIN_URL.'images/teaser-810x262.png'; ?>" /></a>
    <?php else : ?>
    <!-- Start of dataTables -->
    <div id="aklamatorPro-options">
        <div>In order to add new schedule or change existing please <a href="<?php echo $this->aklamator_url ;?>login" target="_blank">login to aklamator</a></div>
        <h2>Automate (Schedule) push messages</h2>

    </div>
    <br>
    <table cellpadding="0" cellspacing="0" border="0"
           class="responsive dynamicTable display table table-bordered" width="100%">
        <thead>
        <tr>

            <th>#</th>
            <th>Title</th>
            <th>Destionation URL</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Scheduled at</th>

        </tr>
        </thead>
        <tbody>
            <?php if(isset($this->api_data->push_scheduled)):

                foreach ($this->api_data->push_scheduled as $key => $item) : ?>
                <tr class="odd">
                    <td><?php echo $key+1; ?></td>
                    <td><?php echo $item->title; ?></td>
                    <td>
                        <a class="btn btn-primary" href = "<?php echo $item->destination_url; ?>" target='_blank' title='Message destionation URL'>url</a>
                    </td>
                    <td>
                        <a href="<?php echo $this->aklamator_url;?>push/automate" class="btn <?php echo ($item->active)? 'btn-success': 'btn-warning'; ?>" target="_blank"><?php echo ($item->active)? 'ON' : 'OFF'; ?>&nbsp;</a>
                    </td>

                    <td><?php echo $item->date_insert; ?></td>
                    <td><?php echo 'Every '. $this->get_week($item->week_day).', '.$item->time ?></td>
                </tr>
            <?php endforeach;
            endif; ?>

        </tbody>
        <tfoot>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Destionation URL</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Scheduled at</th>
        </tr>
        </tfoot>
    </table>

    </div>
<div style="margin-top: 20px; margin-left: 0px; width: 810px;" class="box">
    <!-- Start of dataTables -->
    <div id="aklamatorPro-options">
        <div>In order to add new messages or change existing please <a href="<?php echo $this->aklamator_url ;?>login" target="_blank">login to aklamator</a></div>
        <h2>Sent push messages</h2>
     </div>
    <br>
    <table cellpadding="0" cellspacing="0" border="0"
           class="responsive dynamicTable display table table-bordered" width="100%">
        <thead>
        <tr>

            <th>#</th>
            <th>Title</th>
            <th>Destination URL</th>
            <th>Statistics</th>
            <th>Created At</th>

        </tr>
        </thead>
        <tbody>

        <?php if(isset($this->api_data->push_scheduled)):

        foreach ($this->api_data->push_messages as $key => $item) : ?>
            <tr class="odd">
                <td ><?php echo $key+1; ?></td>
                <td><?php echo $item->title; ?></td>
                <td>
                    <a class="btn btn-primary" href = "<?php echo $item->destination_url; ?>" target='_blank' title='Message destionation URL'>url</a>
                </td>
                <td>
                    <a href="<?php echo $this->aklamator_url;?>push/notifications" class="btn btn-primary" target="_blank">Check</a>
                </td>
                <td><?php echo $item->date_insert; ?></td>

            </tr>
        <?php endforeach;
        endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Destination URL</th>
            <th>Statistics</th>
            <th>Created At</th>
        </tr>
        </tfoot>
    </table>

</div>

<?php endif; ?>