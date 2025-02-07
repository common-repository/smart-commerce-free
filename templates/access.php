<?php
/**
 * 
 * Template Name: Access
 * Template Post Type: page
 *
 * 
 */

$error = null;

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
$link = "https";
else $link = "http";

$link .= "://";

// Append the host(domain name, ip) to the URL.
$link = sanitize_url($link . $_SERVER['HTTP_HOST']);

// Append the requested resource location to the URL
// $link .= $_SERVER['REQUEST_URI'];

if(isset($_POST['products-login'])){
    
    if (empty($_POST['products-name'])) $error = "Please enter a name";
    else if(empty($_POST["products-password"])) $error = "Please enter a password";

    else if(!empty($_POST['products-name']) && !empty($_POST['products-password'])){
        
        $name = sanitize_user($_POST['products-name']);
        $password =  sanitize_text_field($_POST['products-password']);
        $user = wp_authenticate($name, $password);
        if(!is_wp_error($user)){
            if(in_array('product_manager', $user->roles) || in_array('administrator', $user->roles)){

                header("Location:" . $link . "/wp-smart-products?id=1");
                exit;
            } else {
                $error = "The password is wrong or the user doesn't exist";
            }
        } else {
            $error = "Incorrect Username or Password";
        }
        
    }
}

$color = get_option("smt_smart_commerce_brand_color") ? get_option("smt_smart_commerce_brand_color") : "#21759b";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo esc_url(get_site_icon_url()) ?>">

    <?php wp_head() ?>
    <title>Login</title>
</head>
<body>
    <div id="display_errors"><?php if($error) echo esc_html($error) ?></div>
    <div id="login-form-wrapper">
        <?php if(get_option("smt_smart_commerce_logo_url")):?>
            <div id="wp-smart-commerce-brand-logo">
                <img src="<?php echo esc_url(get_option("smt_smart_commerce_logo_url")); ?>" alt="">
            </div>
        <?php endif; ?>
        <form action="" method="post" id="login-form">
            <div class="form-field">
                <label for="products-name" >Username</label>
                <input type="text" name="products-name" >
            </div>

            <div class="form-field">
                <label for="products-password">Password</label>
                <div class="password-container">
                    <input type="password" name="products-password" id="password" >
                    <button id="icon-button" type="button">
                        <i class="fa fa-eye show" id="show-password"></i>
                    </button>
                </div>
            </div>
            <div class="form-field">
                <input type="submit" name="products-login" value="login" id="login-btn">
            </div>
        </form>
    </div>
    <input type="hidden" id="brand-color" value="<?php echo esc_html($color)?>">
   <?php wp_footer() ?>
</body>
</html>


