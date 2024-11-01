<?php
    $link = 'http';
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $link = 'https';
    $link = sanitize_url($link . "://" . $_SERVER['HTTP_HOST']);

    
    if(isset($_POST["save-settings"])){
        if(isset($_POST["smt_smart_commerce_consumer_key"])){
            $consumer_key = sanitize_key($_POST["smt_smart_commerce_consumer_key"]);
            $consumerKeyRegex = "/ck_[a-f0-9]{40}/";
            if(preg_match($consumerKeyRegex, $consumer_key)) update_option("smt_smart_commerce_consumer_key", $consumer_key);
        } 
        if(isset($_POST["smt_smart_commerce_consumer_secret"])){
            $consumer_secret = sanitize_key($_POST["smt_smart_commerce_consumer_secret"]);
            $consumerSecretRegex = "/cs_[a-f0-9]{40}/";
            if(preg_match($consumerSecretRegex, $consumer_secret)) update_option("smt_smart_commerce_consumer_secret", $consumer_secret);
        } 
        if(isset($_POST["smt_smart_commerce_logo_url"])){
            $logo_url = sanitize_url($_POST["smt_smart_commerce_logo_url"]);
            $urlRegex = "/^http(s)?:\/\/(.{1,20}\.)?[a-z]{1,20}\.[a-z]{1,9}(\.[a-z]{1,9})?\/.*$/i";
            if(preg_match($urlRegex, $logo_url)) update_option("smt_smart_commerce_logo_url", $logo_url);
        } 
        if(isset($_POST["smt_smart_commerce_brand_color"])){
            $brand_color = sanitize_hex_color($_POST["smt_smart_commerce_brand_color"]);
            $colorRegex = "/#[0-9a-f]{6}/i";
            if(preg_match($colorRegex, $brand_color)) update_option("smt_smart_commerce_brand_color", $brand_color);
        } 
    }

    if(isset($_POST['register'])){

        $payload = array(
            'domain' => $link,
            'first_name' => sanitize_title($_POST['first_name']),
            'last_name' => sanitize_title($_POST['last_name']),
            'email' => sanitize_email($_POST['email'])
        );

        $args = array(
            'body' => $payload
        );

        $request = wp_remote_post("https://api.smartmetatec.com/api/free/commerce", $args);
        $response = wp_remote_retrieve_body( $request );


        $jsonResponse = json_decode($response, true);
        if($jsonResponse['pass']) update_option("smt_smart_commerce_free_register", "true");
       
    }


?>

<div class="wrap" id="smt-admin-page">
        <div id="wp-smart-commerce-header-container">
            <img src="<?php echo esc_url(plugins_url("assets/SCColorLogo150X150.png", __FILE__)) ?>" alt="">
            <h1>WP Smart Commerce Dashboard</h1>
        </div>
        <?php if(get_option("smt_smart_commerce_free_register")): ?>
            <form method="post" action="">
                <div>
                    <label>Consumer Key</label>
                    <input type="text" name="smt_smart_commerce_consumer_key" value="<?php echo esc_attr(get_option('smt_smart_commerce_consumer_key')) ?>" />
                </div>

                <div>
                    <label >Consumer Secret</label>
                    <input type="text" name="smt_smart_commerce_consumer_secret" value="<?php echo esc_attr(get_option('smt_smart_commerce_consumer_secret')) ?>" />
                </div>

                <div>
                    <label for="">Enter Logo URL</label>
                    <input type="text" name="smt_smart_commerce_logo_url" value="<?php echo esc_attr(get_option("smt_smart_commerce_logo_url"))?>">
                </div>

                <div>
                    <label for="wp_smart_products_color">Enter Brand Color</label>
                    <?php // This input is placed in a div because of the color pick eye dropper chrome extension?>
                    <div>
                        <input type="color" name="smt_smart_commerce_brand_color" id="" value="<?php echo get_option("smt_smart_commerce_brand_color") ? esc_attr(get_option("smt_smart_commerce_brand_color")): esc_attr("#21759b")?>">
                    </div>
                    
                </div>

                <div class="submit">
                    <input type="submit" class="button-primary" value="Save" name="save-settings"/>
                </div>

            </form>

            <div>
                <a href="<?php echo esc_url($link . "/wp-smart-login") ?>" target="_blank">Go To Products</a>
            </div>

            <section id="guide">
                <div>
                    <h3>How to setup plugin</h3>
                    <ol>
                        <li>In the Admin Dashboard go to: <b>WooCommerce > Settings > Advanced > <a href="<?php echo esc_url($link . "/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys") ?>" target="_blank">REST API</a></b>.</li>
                        <li>Click on <b>Add Key</b>.</li>
                        <li>Enter a description for the key such as: WP Smart Commerce Integration.</li>
                        <li>Make sure the correct User is selected and set the Permissions to <b>Read/Write</b>.</li>
                        <li>Click on <b>Generate Key</b>.</li>
                        <li>Copy both the <b>Consumer Key</b> and <b>Consumer Secret</b> and put them somewhere safe.</li>
                        <li>In the <b>Smart Commerce Admin Dashboard</b> paste the keys in the correct fields as they are labelled.</li>
                        <li>Optionally enter your Logo URL and brand color and click Save.</li>
                    </ol>
                </div>
                <div>
                    <h3>Create a User To Use The Plugin (Administrators already have access)</h3>
                    <ol>
                        <li>Go to Users and Click on <b> <a href="<?php echo esc_url($link . "/wp-admin/user-new.php") ?>" target="_blank">Add New</a></b>.</li>
                        <li>Enter the <b>Username</b> and <b>Email</b> as well as any other optional fields.</li>
                        <li>Click on <b>Generate password</b> or set your own.</li>
                        <li>Under <b>Role</b> select the <b>Product Manager</b> role. (Only Product Managers and Administrators can use the plugin).</li>
                        <li>Click on <b>Add New User</b>.</li>
                    </ol>
                </div>


                
            </section>
        <?php endif ?>
        <?php if(!get_option("smt_smart_commerce_free_register")): ?>
            <main class="wp_commerce_free_register">
                <form action="" method="post">
                    <div>
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div>
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="text" name="email" required>
                    </div>
                    <div>
                        <input type="submit" name="register" value="Create Free Account">
                    </div>
                </form>
            </main>
        <?php endif ?>
 
    </div>